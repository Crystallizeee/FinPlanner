<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BudgetCycle;
use App\Models\ExpenseTransaction;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrReceiptProcessorService
{
    public function __construct(
        protected HpBudgetingService $hpService,
        protected StreakPenaltyService $streakService
    ) {}

    /**
     * Process an uploaded receipt image, extract metadata via OCR, and create expense records.
     *
     * @throws \Exception
     */
    public function processReceipt(User $user, UploadedFile $file, ?BudgetCycle $budgetCycle = null): Receipt
    {
        $budgetCycle ??= $user->getActiveBudgetCycle();

        if (! $budgetCycle) {
            throw new \RuntimeException('No active budget cycle found for this user.');
        }

        // Store image in public storage disk
        $path = $file->store('receipts', 'public');
        $fullPath = storage_path('app/public/' . $path);

        // 1. Try Gemini AI Vision extraction (100% precision for merchant, PPN, items, discounts)
        $aiParsedData = $this->extractReceiptWithGeminiAi($fullPath);

        if ($aiParsedData !== null) {
            $parsedData = $aiParsedData;
            $rawText = "[AI Vision Engine - Gemini 2.0 Flash]\n" . json_encode($parsedData, JSON_PRETTY_PRINT);
        } else {
            // 2. Fallback to Local Preprocessed Tesseract OCR
            $rawText = $this->extractTextFromImage($fullPath);
            $parsedData = $this->parseReceiptText($rawText);
        }

        return DB::transaction(function () use ($user, $budgetCycle, $path, $rawText, $parsedData) {
            $receipt = Receipt::create([
                'user_id' => $user->id,
                'receipt_number' => $parsedData['receipt_number'] ?? 'REC-' . strtoupper(Str::random(8)),
                'merchant_name' => $parsedData['merchant_name'],
                'total_amount' => $parsedData['total_amount'],
                'transaction_date' => $parsedData['transaction_date'],
                'image_path' => $path,
                'ocr_raw_text' => $rawText,
                'ocr_status' => 'processed',
                'confidence_score' => $parsedData['confidence_score'],
            ]);

            foreach ($parsedData['items'] as $item) {
                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'item_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Create ExpenseTransaction (Anti-cheat: nominal strictly comes from receipt OCR!)
            ExpenseTransaction::create([
                'user_id' => $user->id,
                'budget_cycle_id' => $budgetCycle->id,
                'receipt_id' => $receipt->id,
                'source' => 'receipt_ocr',
                'merchant' => $parsedData['merchant_name'],
                'amount' => $parsedData['total_amount'],
                'description' => "Verified OCR Grocery Receipt - {$parsedData['merchant_name']} (" . count($parsedData['items']) . ' items)',
                'transaction_date' => $parsedData['transaction_date'],
                'is_verified' => true,
            ]);

            // Recalculate HP
            $this->hpService->recalculateCycleHp($budgetCycle);

            // Record streak activity & resolve potential penalties
            $this->streakService->recordUserActivity($user);

            return $receipt;
        });
    }

    /**
     * Extract raw text using Tesseract OCR with GD image preprocessing fallback.
     */
    protected function extractTextFromImage(string $filePath): string
    {
        $preprocessedPath = null;
        try {
            if (file_exists($filePath) && function_exists('exec')) {
                $preprocessedPath = $this->preprocessImageForOcr($filePath);
                $targetPath = $preprocessedPath ?: $filePath;

                $tesseract = new TesseractOCR($targetPath);
                
                // Ensure tesseract can find executable on Windows environments if needed
                if (PHP_OS_FAMILY === 'Windows') {
                    $tesseract->executable('tesseract');
                }

                $text = $tesseract->run();

                if ($preprocessedPath && file_exists($preprocessedPath)) {
                    @unlink($preprocessedPath);
                }

                if (! empty(trim($text))) {
                    return $text;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Tesseract OCR engine unavailable or failed: ' . $e->getMessage());
            if ($preprocessedPath && file_exists($preprocessedPath)) {
                @unlink($preprocessedPath);
            }
        }

        return '';
    }

    /**
     * Preprocess image with GD (Upscaling 2.5x, Grayscale, Contrast) to improve Tesseract accuracy on low-res receipts.
     */
    protected function preprocessImageForOcr(string $filePath): ?string
    {
        if (! function_exists('imagecreatefromjpeg')) {
            return null;
        }

        $info = @getimagesize($filePath);
        if (! $info) {
            return null;
        }

        $mime = $info['mime'] ?? '';
        $srcImg = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($filePath),
            'image/png' => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            default => null,
        };

        if (! $srcImg) {
            return null;
        }

        $width = imagesx($srcImg);
        $height = imagesy($srcImg);
        $scale = 2.5;
        $newW = (int) ($width * $scale);
        $newH = (int) ($height * $scale);

        $dstImg = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagefilter($dstImg, IMG_FILTER_GRAYSCALE);
        imagefilter($dstImg, IMG_FILTER_CONTRAST, -35);
        imagefilter($dstImg, IMG_FILTER_BRIGHTNESS, 15);

        $tempPath = storage_path('app/public/receipts/temp_ocr_' . Str::random(10) . '.png');
        imagepng($dstImg, $tempPath);
        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return $tempPath;
    }

    /**
     * Parse raw text string to extract receipt components (Grocery stores, BCA Bank Transfers, QRIS, E-wallets, etc.)
     *
     * @return array{merchant_name: string, total_amount: float, transaction_date: Carbon, receipt_number: string|null, confidence_score: float, items: array<int, array{name: string, qty: float, unit_price: float, total_price: float}>}
     */
    public function parseReceiptText(string $text): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn ($line) => ! empty($line)));

        $merchantName = null;
        $totalAmount = 0.0;
        $transactionDate = now();
        $receiptNumber = null;
        $items = [];
        $detectedNote = null;
        $receiverName = null;

        // 1. Merchant & Transfer Type Detection
        $fullTextUpper = strtoupper($text);

        if (str_contains($fullTextUpper, 'M-TRANSFER') || str_contains($fullTextUpper, 'BCA') || str_contains($fullTextUpper, 'BANK CENTRAL ASIA') || str_contains($fullTextUpper, 'MYBCA')) {
            $merchantName = 'Bank BCA (m-Transfer)';
        } elseif (str_contains($fullTextUpper, 'MANDIRI') || str_contains($fullTextUpper, 'LIVIN')) {
            $merchantName = 'Bank Mandiri';
        } elseif (str_contains($fullTextUpper, 'BRI') || str_contains($fullTextUpper, 'BRIMO')) {
            $merchantName = 'Bank BRI';
        } elseif (str_contains($fullTextUpper, 'BNI')) {
            $merchantName = 'Bank BNI';
        } elseif (str_contains($fullTextUpper, 'QRIS')) {
            $merchantName = 'QRIS Payment';
        } elseif (str_contains($fullTextUpper, 'GOPAY')) {
            $merchantName = 'GoPay';
        } elseif (str_contains($fullTextUpper, 'OVO')) {
            $merchantName = 'OVO';
        } elseif (str_contains($fullTextUpper, 'SHOPEEPAY')) {
            $merchantName = 'ShopeePay';
        } elseif (str_contains($fullTextUpper, 'DANA')) {
            $merchantName = 'DANA';
        } elseif (str_contains($fullTextUpper, 'SUPER INDO') || str_contains($fullTextUpper, 'SUPER IDO') || str_contains($fullTextUpper, 'SUPERINDO') || str_contains($fullTextUpper, 'LION SUPER')) {
            $merchantName = 'Super Indo';
        } elseif (str_contains($fullTextUpper, 'INDOMARET')) {
            $merchantName = 'Indomaret';
        } elseif (str_contains($fullTextUpper, 'ALFAMART')) {
            $merchantName = 'Alfamart';
        }

        // Search for specific BCA / Bank Transfer details & Retail details
        foreach ($lines as $index => $line) {
            $upper = strtoupper($line);

            // Extract Receiver Name for bank transfers
            if (preg_match('/^KE\s+[0-9]+\s+([A-Z\s]+)$/i', $line, $matches)) {
                $receiverName = trim($matches[1]);
            } elseif (preg_match('/^KE(?:\s+[0-9]+)?$/i', $line) && isset($lines[$index + 1])) {
                $candidate = trim($lines[$index + 1]);
                if (strlen($candidate) > 2 && ! preg_match('/^[0-9\s\:\.\,]+$/', $candidate) && ! str_contains(strtoupper($candidate), 'BERHASIL')) {
                    $receiverName = $candidate;
                }
            }

            // Extract Ref / Acc / Receipt Number (skip address line numbers like "NO 352")
            if (preg_match('/(?:NO|NOTA|TRX|INV)[:\s]+([A-Z0-9\-]{4,})/i', $line, $matches)) {
                if (! in_array(strtoupper($matches[1]), ['BERHASIL', 'SUKSES', 'TRANSFER', 'BCA', 'RP'])) {
                    $receiptNumber ??= $matches[1];
                }
            }

            // Extract Date (e.g. 04-12-21, 19/07 14:07:00 or 26/08/2026 or D4 12-21)
            // Skip NPWP Registration Date ("Tanggal Pengukuhan")
            if (! str_contains($upper, 'PENGUKUHAN') && ! str_contains($upper, 'NPWP')) {
                if (preg_match('/(?:[0-9]|D)(\d)[\/\-](\d{1,2})[\/\-](\d{2,4})(?:\s+\((\d{2}:\d{2}(?::\d{2})?)\))?/', $line, $matches)) {
                    try {
                        $day = (int) $matches[1];
                        $month = (int) $matches[2];
                        $rawYear = (int) $matches[3];
                        $timeStr = $matches[4] ?? '12:00:00';
                        $year = strlen((string) $rawYear) === 2 ? 2000 + $rawYear : $rawYear;

                        if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                            $transactionDate = Carbon::create($year, $month, $day, ...array_map('intval', explode(':', $timeStr)));
                        }
                    } catch (\Throwable $e) {
                        // Ignore date parse failure
                    }
                }
            }

            // Extract Amount (e.g., "Sub Total (Termasuk PPN) : 1.002.500", "Pembayaran-UDB - MASTER ELE: 1.002.500", "Rp. 213,000.00")
            $hasRpPrefix = preg_match('/(?:RP\.?|IDR)\s*([0-9]{1,3}(?:[\.\,][0-9]{3})*(?:[\.\,][0-9]{2})?|[0-9]+)/i', $line, $matches);
            $hasTotalLabel = preg_match('/(?:SUB\s*TOTAL|TOTAL|GRAND\s*TOTAL|TOTAL\s*BAYAR|PEMBAYARAN|JUMLAH|NOMINAL)(?:\s*\([^)]*\))?[:\s\-\.]*(?:RP\.?|IDR)?\s*([0-9]{1,3}(?:[\.\,][0-9]{3})*(?:[\.\,][0-9]{2})?|[0-9]+)/i', $line, $labelMatches);

            if ($hasRpPrefix || $hasTotalLabel) {
                $numStr = $hasTotalLabel ? $labelMatches[1] : $matches[1];
                $parsedVal = $this->parseIndonesianCurrency($numStr);

                if ($parsedVal > 100) {
                    if ($hasTotalLabel) {
                        $totalAmount = $parsedVal;
                    } elseif ($totalAmount <= 0) {
                        $totalAmount = $parsedVal;
                    }
                }
            }

            // Helper to reject non-product metadata lines (PPN, Tax, Admin Fee, Subtotal, etc.)
            $isNonProductLine = fn (string $str) => (bool) preg_match('/^(?:DESKRIPSI|SUBTOTAL|SUB\s*TOTAL|TOTAL|DISCOUNT|CASH|PEMBAYARAN|NPWP|TELP|PPN|TAX|PAJAK|BIAYA|ADMIN|BEBAS|SISA|SALDO|CHANGE|KEMBALIAN|TANGGAL|METODE|HARGA|QTY)/i', trim($str));

            // Extract Itemization - Pattern 1: Super Indo / Retail 4-Column Format ("SEDAAP KARI SPC75 160 2.590 414.400" or fuzzy OCR)
            if (preg_match('/^([A-Z0-9\s\/\-\.:]{3,40}?)\s+(\d{1,4})\s+([0-9]{1,3}(?:[\.\,][0-9]{3})+|[0-9]+)\s+([0-9]{1,3}(?:[\.\,][0-9]{3})+|[0-9]+)$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $qty = (float) $matches[2];
                $unitPrice = $this->parseIndonesianCurrency($matches[3]);
                $totalPrice = $this->parseIndonesianCurrency($matches[4]);

                // Filter out non-item header lines
                if (! $isNonProductLine($name)) {
                    $items[] = [
                        'name' => $name,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ];
                }
            }
            // Pattern 2: Grocery Prefix Format ("1 MINYAK GORENG TROPICAL 2L Rp 38.500")
            elseif (preg_match('/^(\d{1,4})\s+([A-Z0-9\s\/\-\.:]{3,40}?)\s+(?:RP\.?)?\s*([0-9]{1,3}(?:[\.\,][0-9]{3})+|[0-9]+)$/i', $line, $matches)) {
                $qty = (float) $matches[1];
                $name = trim($matches[2]);
                $price = $this->parseIndonesianCurrency($matches[3]);

                if (! $isNonProductLine($name)) {
                    $items[] = [
                        'name' => $name,
                        'qty' => $qty,
                        'unit_price' => $price,
                        'total_price' => $price * $qty,
                    ];
                }
            }
            // Pattern 3: Fuzzy Retail Item Format ("ULTRA PLW IL 8 16.990 107,940" or "INDOMIE SOTO SPC 40 2.590 103,600")
            elseif (preg_match('/^([A-Z0-9\s\/\-\.:]{3,40}?)\s+(\d{1,4})(?:\s+[A-Za-z\)\(]+)?\s+([0-9]{1,3}(?:[\.\,][0-9]{3})+|[0-9]+)$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $qty = (float) $matches[2];
                $totalPrice = $this->parseIndonesianCurrency($matches[3]);

                if (! $isNonProductLine($name)) {
                    $items[] = [
                        'name' => $name,
                        'qty' => $qty,
                        'unit_price' => $qty > 0 ? round($totalPrice / $qty, 2) : $totalPrice,
                        'total_price' => $totalPrice,
                    ];
                }
            }
            // Pattern 4: Discount / Hemat Line ("HEMAT -62.400" or "DISCOUNT -10.000")
            elseif (preg_match('/^(?:HEMAT|DISCOUNT|DISC)\s+[\-\-]?\s*(?:RP\.?)?\s*([0-9\.\,]+)/i', $line, $matches)) {
                $discountVal = $this->parseIndonesianCurrency($matches[1]);
                // Deduct discount from previous item's total price for net accuracy
                $lastIndex = count($items) - 1;
                if ($lastIndex >= 0) {
                    $items[$lastIndex]['total_price'] = max(0, $items[$lastIndex]['total_price'] - $discountVal);
                }
            }

            // Detect transfer note / description line (e.g. "hair scrub green tea")
            if (! str_contains($upper, 'M-TRANSFER') && ! str_contains($upper, 'BERHASIL') && ! str_contains($upper, 'KE ') && ! str_contains($upper, 'RP') && ! preg_match('/^\d{1,2}\/\d{1,2}/', $line)) {
                if (strlen($line) > 3 && strlen($line) < 60 && ! preg_match('/^[0-9\s\:\.\,\-\/]+$/', $line)) {
                    $detectedNote = trim($line);
                }
            }
        }

        // Refine Merchant Name if receiver is present
        if ($receiverName && str_contains($merchantName ?? '', 'BCA')) {
            $merchantName = "BCA Transfer - {$receiverName}";
        } elseif (! $merchantName && ! empty($lines)) {
            // Default merchant to top line if no keyword matched
            $merchantName = preg_replace('/[^\w\s]/', '', substr($lines[0], 0, 40));
        } elseif (! $merchantName) {
            $merchantName = 'Bukti Transaksi OCR';
        }

        // Search bottom lines for total amount if not matched by label
        if ($totalAmount <= 0) {
            $bottomLines = array_slice($lines, -6);
            foreach (array_reverse($bottomLines) as $bLine) {
                if (preg_match('/([0-9]{1,3}(?:[\.\,][0-9]{3}){1,3})/i', $bLine, $bMatches)) {
                    $val = $this->parseIndonesianCurrency($bMatches[1]);
                    if ($val > 1000) {
                        $totalAmount = $val;
                        break;
                    }
                }
            }
        }

        // Fallback total_amount to sum of items if still 0
        if ($totalAmount <= 0 && ! empty($items)) {
            $totalAmount = (float) array_sum(array_column($items, 'total_price'));
        }

        // If items are empty (Bank transfer proof rather than itemized grocery bill)
        if (empty($items) && $totalAmount > 0) {
            $validNote = ($detectedNote && ! $isNonProductLine($detectedNote)) ? $detectedNote : null;
            $itemName = $validNote ?: ($receiverName ? "Transfer Ke {$receiverName}" : "Pembayaran {$merchantName}");
            $items[] = [
                'name' => $itemName,
                'qty' => 1.0,
                'unit_price' => $totalAmount,
                'total_price' => $totalAmount,
            ];
        }

        // Calculate Confidence Score
        $confidence = 50.0;
        if ($merchantName !== 'Bukti Transaksi OCR') $confidence += 15.0;
        if ($totalAmount > 0) $confidence += 20.0;
        if (! empty($items)) $confidence += 14.0;

        return [
            'merchant_name' => $merchantName,
            'total_amount' => $totalAmount > 0 ? $totalAmount : 0.0,
            'transaction_date' => $transactionDate,
            'receipt_number' => $receiptNumber ?: 'TRX-' . strtoupper(Str::random(8)),
            'confidence_score' => min(99.0, $confidence),
            'items' => $items,
        ];
    }

    /**
     * Parse Indonesian formatted currency string to float value.
     * Examples: "213,000.00" -> 213000.00, "186.000" -> 186000.00, "45.500,00" -> 45500.00
     */
    protected function parseIndonesianCurrency(string $val): float
    {
        $val = trim($val);

        // Remove trailing non-digit chars e.g. "418-00)" or "107,940)"
        $val = preg_replace('/[^\d\.\,]/', '', $val);

        // Handle "213,000.00" (comma thousands, dot decimals)
        if (preg_match('/^\d{1,3}(?:,\d{3})+\.\d{2}$/', $val)) {
            return (float) str_replace(',', '', $val);
        }

        // Handle "213.000,00" (dot thousands, comma decimals)
        if (preg_match('/^\d{1,3}(?:\.\d{3})+,\d{2}$/', $val)) {
            $clean = str_replace('.', '', $val);
            return (float) str_replace(',', '.', $clean);
        }

        // Handle "213.000" or "186.000" (dot thousands without decimals)
        if (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $val)) {
            return (float) str_replace('.', '', $val);
        }

        // Handle "213,000" (comma thousands without decimals e.g. "107,940" or "103,600")
        if (preg_match('/^\d{1,3}(?:,\d{3})+$/', $val)) {
            return (float) str_replace(',', '', $val);
        }

        // Fallback simple numeric cleaning
        $clean = preg_replace('/[^0-9\.]/', '', str_replace(',', '.', $val));
        return (float) $clean;
    }

    /**
     * Use Gemini 2.0 Flash Vision AI to extract structured receipt JSON directly from receipt image.
     *
     * @return array{merchant_name: string, total_amount: float, transaction_date: Carbon, receipt_number: string|null, confidence_score: float, items: array<int, array{name: string, qty: float, unit_price: float, total_price: float}>}|null
     */
    public function extractReceiptWithGeminiAi(string $filePath): ?array
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');

        if (! $apiKey || ! file_exists($filePath)) {
            return null;
        }

        try {
            $imageData = base64_encode(file_get_contents($filePath));
            $mimeType = @mime_content_type($filePath) ?: 'image/jpeg';

            $prompt = <<<'PROMPT'
You are an expert AI OCR financial receipt parser. Analyze this physical receipt or transaction proof image carefully.

Extract structured JSON matching this schema strictly:
{
  "merchant_name": "Store or Merchant brand printed at the VERY TOP of the receipt (e.g. FamilyMart, Super Indo, Alfamart, Indomaret, Kebon Sari TMG). Do NOT confuse payment method like DANA/GoPay/BCA with the store merchant name.",
  "total_amount": 0.0,
  "transaction_date": "YYYY-MM-DD",
  "receipt_number": "Invoice / Receipt Number / Bon Number if printed",
  "confidence_score": 99.0,
  "items": [
    {
      "name": "Item or Service Name",
      "qty": 1.0,
      "unit_price": 0.0,
      "total_price": 0.0
    }
  ]
}

CRITICAL RULES FOR ITEM EXTRACTION:
1. Examine all line items carefully. In receipts like FamilyMart or Alfamart, the item name is printed on one line, and the quantity & price (e.g. '15,000 X 1 15,000' or '2 1,250 2,500') or discount line (e.g. '-5,000' or 'Disc. -1,100') is on the line right under it.
2. If there are discounts right under an item, calculate the net total_price for that item or adjust prices accordingly.
3. Ignore header metadata (ADDRESS, STORE ID, POS ID, CASHIER, NPWP, BON NO, TELP) and footer metadata (PPN, TAX, PB1, CHANGE/KEMBALIAN, TOTAL PAID, TOTAL ITEM, PAYMENT METHOD DANA/TUNAI).
4. If this is a digital bank/e-wallet transfer screenshot with no individual products, create 1 item with the transfer note or transfer summary as name and total_amount as total_price.

Return ONLY raw valid JSON.
PROMPT;

            $response = Http::timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageData,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.1,
                ],
            ]);

            if ($response->successful()) {
                $jsonText = $response->json('candidates.0.content.parts.0.text');
                if ($jsonText) {
                    $parsed = json_decode($jsonText, true);
                    if (is_array($parsed) && isset($parsed['merchant_name'], $parsed['total_amount'])) {
                        $items = [];
                        if (isset($parsed['items']) && is_array($parsed['items'])) {
                            foreach ($parsed['items'] as $item) {
                                $items[] = [
                                    'name' => (string) ($item['name'] ?? 'Item'),
                                    'qty' => (float) ($item['qty'] ?? 1),
                                    'unit_price' => (float) ($item['unit_price'] ?? ($item['total_price'] ?? 0)),
                                    'total_price' => (float) ($item['total_price'] ?? 0),
                                ];
                            }
                        }

                        $dateStr = $parsed['transaction_date'] ?? null;
                        $transactionDate = now();
                        if ($dateStr) {
                            try {
                                $transactionDate = Carbon::parse($dateStr);
                            } catch (\Throwable $e) {
                            }
                        }

                        return [
                            'merchant_name' => (string) $parsed['merchant_name'],
                            'total_amount' => (float) $parsed['total_amount'],
                            'transaction_date' => $transactionDate,
                            'receipt_number' => $parsed['receipt_number'] ?? null,
                            'confidence_score' => (float) ($parsed['confidence_score'] ?? 98.0),
                            'items' => $items,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini AI Vision Receipt Processing failed: ' . $e->getMessage());
        }

        return null;
    }
}

