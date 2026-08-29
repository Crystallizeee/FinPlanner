<?php

namespace Tests\Feature;

use App\Livewire\DashboardComponent;
use App\Models\BudgetCycle;
use App\Models\Receipt;
use App\Models\User;
use App\Services\OcrReceiptProcessorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AntiCheatReceiptOcrTest extends TestCase
{
    use RefreshDatabase;

    public function test_ocr_processor_extracts_super_indo_receipt_and_creates_expense(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Warrior User',
            'email' => 'warrior@example.com',
            'password' => bcrypt('password'),
            'hp_current' => 100,
        ]);

        $budgetCycle = BudgetCycle::create([
            'user_id' => $user->id,
            'name' => 'Test Budget Cycle',
            'period_type' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'planned_budget' => 5000000.00,
            'spent_amount' => 0.00,
            'hp_level' => 100,
        ]);

        $file = UploadedFile::fake()->image('superindo_receipt.jpg');

        /** @var OcrReceiptProcessorService $ocrService */
        $ocrService = app(OcrReceiptProcessorService::class);
        $receipt = $ocrService->processReceipt($user, $file, $budgetCycle);

        $this->assertDatabaseHas('receipts', [
            'id' => $receipt->id,
            'user_id' => $user->id,
            'merchant_name' => $receipt->merchant_name,
            'total_amount' => $receipt->total_amount,
        ]);

        $this->assertDatabaseHas('expense_transactions', [
            'user_id' => $user->id,
            'budget_cycle_id' => $budgetCycle->id,
            'source' => 'receipt_ocr',
            'amount' => $receipt->total_amount,
        ]);
    }

    public function test_ocr_parser_correctly_parses_bca_mtransfer_receipt(): void
    {
        $rawBcaOcrText = <<<'TEXT'
m-Transfer

m-Transfer :
BERHASIL

19/07 14:07:00

Ke 8890439633
LINA RIAWATI

Rp. 213,000.00
hair scrub green tea
TEXT;

        /** @var OcrReceiptProcessorService $ocrService */
        $ocrService = app(OcrReceiptProcessorService::class);
        $parsed = $ocrService->parseReceiptText($rawBcaOcrText);

        $this->assertStringContainsString('BCA', $parsed['merchant_name']);
        $this->assertStringContainsString('LINA RIAWATI', $parsed['merchant_name']);
        $this->assertEquals(213000.00, $parsed['total_amount']);
        $this->assertNotEmpty($parsed['items']);
        $this->assertEquals('hair scrub green tea', $parsed['items'][0]['name']);
    }

    public function test_ocr_parser_correctly_parses_super_indo_4column_itemized_receipt(): void
    {
        $superIndoText = <<<'TEXT'
PT LION SUPER INDO
NPWP : 01.781.372.6-046.000
Tanggal Pengukuhan : 06-06-97
JL. TIDAR NO 352
KEL TEMBOK DUKUH, KEC. BUBUT
SURABAYA JAWA TIMUR
Telp : 031-99255175 / 99255634

04-12-21 (11:55:25) 710 06 No:00086
DESKRIPSI QTY HARGA TOTAL
========================================
SEDAAP KARI SPC75 160 2.590 414.400
HEMAT -62.400
ULTRA PLN 1L 6 16.990 101.940
HEMAT -18.240
INDOMI GORENG BW/ 160 2.600 416.000
HEMAT -38.600
INDOMIE SOTO SPC 40 2.590 103.600
HEMAT -14.400
INDOMIE SOTO SPC 40 2.590 103.600
HEMAT -14.400
SEDAAP KARI SPC75 5 2.590 12.950
HEMAT -1.950
========================================
Sub Total (Termasuk PPN) : 1.002.500

Pembayaran-UDB - MASTER ELE: 1.002.500
Nomor : 6017-56**-****-5057
TEXT;

        /** @var OcrReceiptProcessorService $ocrService */
        $ocrService = app(OcrReceiptProcessorService::class);
        $result = $ocrService->parseReceiptText($superIndoText);

        $this->assertEquals('Super Indo', $result['merchant_name']);
        $this->assertEquals(1002500.0, $result['total_amount']);
        $this->assertEquals('00086', $result['receipt_number']);
        $this->assertCount(6, $result['items']);
        $this->assertEquals('SEDAAP KARI SPC75', $result['items'][0]['name']);
        $this->assertEquals(160, $result['items'][0]['qty']);
        $this->assertEquals(352000.0, $result['items'][0]['total_price']);
        $this->assertEquals('ULTRA PLN 1L', $result['items'][1]['name']);
        $this->assertEquals(83700.0, $result['items'][1]['total_price']);
    }

    public function test_ocr_processor_ignores_ppn_and_admin_fee_metadata_lines(): void
    {
        $danaText = <<<'TEXT'
DANA
PEMBAYARAN BERHASIL
ORDER ID : 2026082600129
pPN : 0
BIAYA ADMIN : 0
TOTAL BAYAR : 24.000
TEXT;

        /** @var OcrReceiptProcessorService $ocrService */
        $ocrService = app(OcrReceiptProcessorService::class);
        $result = $ocrService->parseReceiptText($danaText);

        $this->assertEquals('DANA', $result['merchant_name']);
        $this->assertEquals(24000.0, $result['total_amount']);
        $this->assertCount(0, array_filter($result['items'], fn ($i) => str_contains(strtoupper($i['name']), 'PPN')));
    }

    public function test_user_can_edit_receipt_merchant_nominal_and_itemized_breakdown(): void
    {
        $user = User::factory()->create();
        $receipt = Receipt::create([
            'user_id' => $user->id,
            'merchant_name' => 'Wrong Merchant',
            'total_amount' => 50000.00,
            'image_path' => 'receipts/test.jpg',
            'transaction_date' => now(),
            'ocr_status' => 'processed',
            'confidence_score' => 80.0,
        ]);

        $receipt->items()->create([
            'item_name' => 'Wrong Item 1',
            'quantity' => 1,
            'unit_price' => 50000.00,
            'total_price' => 50000.00,
        ]);

        Livewire::test(DashboardComponent::class)
            ->call('viewReceiptDetails', $receipt->id)
            ->call('startEditingReceipt')
            ->set('editMerchantName', 'Super Indo Tidar')
            ->set('editItems', [
                [
                    'id' => null,
                    'name' => 'Minyak Goreng Tropical 2L',
                    'qty' => 2,
                    'unit_price' => 38500.00,
                    'total_price' => 77000.00,
                ],
                [
                    'id' => null,
                    'name' => 'Susu Ultra Milk 1L',
                    'qty' => 3,
                    'unit_price' => 18000.00,
                    'total_price' => 54000.00,
                ],
            ])
            ->call('recalculateItemsTotal')
            ->assertSet('editTotalAmount', 131000.00)
            ->call('saveReceiptCorrection');

        $receipt->refresh();

        $this->assertEquals('Super Indo Tidar', $receipt->merchant_name);
        $this->assertEquals(131000.00, $receipt->total_amount);
        $this->assertEquals('manually_corrected', $receipt->ocr_status);
        $this->assertCount(2, $receipt->items);
        $this->assertEquals('Minyak Goreng Tropical 2L', $receipt->items[0]->item_name);
        $this->assertEquals(77000.00, $receipt->items[0]->total_price);
        $this->assertEquals('Susu Ultra Milk 1L', $receipt->items[1]->item_name);
    }
}

