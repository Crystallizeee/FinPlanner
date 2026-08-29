<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\BudgetCycle;
use App\Models\ExpenseTransaction;
use App\Models\Receipt;
use App\Services\HpBudgetingService;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Global receipt detail & edit modal.
 * Triggered from any page via: $dispatch('openReceiptModal', { receiptId: id })
 */
class ReceiptDetailModal extends Component
{
    public bool $show = false;
    public bool $isEditing = false;

    public ?Receipt $receipt = null;

    public string $editMerchantName = '';
    public float $editTotalAmount = 0.0;
    public array $editItems = [];

    protected function rules(): array
    {
        return [
            'editMerchantName'         => 'required|string|max:255',
            'editTotalAmount'          => 'required|numeric|min:1',
            'editItems'                => 'nullable|array',
            'editItems.*.name'         => 'required|string|max:255',
            'editItems.*.qty'          => 'required|numeric|min:0.01',
            'editItems.*.unit_price'   => 'required|numeric|min:0',
            'editItems.*.total_price'  => 'required|numeric|min:0',
        ];
    }

    #[On('openReceiptModal')]
    public function open(int $receiptId): void
    {
        $receipt = Receipt::with('items')->find($receiptId);

        if (! $receipt) {
            return;
        }

        $this->receipt = $receipt;
        $this->isEditing = false;
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->isEditing = false;
        $this->receipt = null;
        $this->editItems = [];
    }

    public function startEditing(): void
    {
        if (! $this->receipt) {
            return;
        }

        $this->editMerchantName = $this->receipt->merchant_name;
        $this->editTotalAmount  = (float) $this->receipt->total_amount;
        $this->editItems = $this->receipt->items->map(fn ($item) => [
            'id'          => $item->id,
            'name'        => $item->item_name,
            'qty'         => (float) $item->quantity,
            'unit_price'  => (float) $item->unit_price,
            'total_price' => (float) $item->total_price,
        ])->toArray();

        if (empty($this->editItems)) {
            $this->addItemRow();
        }

        $this->isEditing = true;
    }

    public function cancelEditing(): void
    {
        $this->isEditing = false;
        $this->editItems = [];
    }

    public function addItemRow(): void
    {
        $this->editItems[] = [
            'id'          => null,
            'name'        => '',
            'qty'         => 1.0,
            'unit_price'  => 0.0,
            'total_price' => 0.0,
        ];
    }

    public function removeItemRow(int $index): void
    {
        unset($this->editItems[$index]);
        $this->editItems = array_values($this->editItems);
        $this->recalculateTotal();
    }

    public function updatedEditItems($value, string $key): void
    {
        $parts = explode('.', $key);

        if (count($parts) === 2 && in_array($parts[1], ['qty', 'unit_price'])) {
            $index = (int) $parts[0];

            if (isset($this->editItems[$index])) {
                $qty       = (float) ($this->editItems[$index]['qty'] ?? 0);
                $unitPrice = (float) ($this->editItems[$index]['unit_price'] ?? 0);
                $this->editItems[$index]['total_price'] = round($qty * $unitPrice, 2);
                $this->recalculateTotal();
            }
        } elseif (count($parts) === 2 && $parts[1] === 'total_price') {
            $this->recalculateTotal();
        }
    }

    private function recalculateTotal(): void
    {
        if (! empty($this->editItems)) {
            $sum = (float) array_sum(array_column($this->editItems, 'total_price'));

            if ($sum > 0) {
                $this->editTotalAmount = $sum;
            }
        }
    }

    public function saveCorrection(HpBudgetingService $hpService): void
    {
        $this->validate();

        if (! $this->receipt) {
            return;
        }

        $this->receipt->update([
            'merchant_name' => $this->editMerchantName,
            'total_amount'  => $this->editTotalAmount,
            'ocr_status'    => 'manually_corrected',
        ]);

        // Sync item breakdown
        $this->receipt->items()->delete();
        foreach ($this->editItems as $item) {
            if (! empty(trim($item['name']))) {
                $this->receipt->items()->create([
                    'item_name'   => trim($item['name']),
                    'quantity'    => $item['qty'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }
        }

        // Sync corresponding expense transaction
        ExpenseTransaction::where('receipt_id', $this->receipt->id)->update([
            'merchant'    => $this->editMerchantName,
            'amount'      => $this->editTotalAmount,
            'description' => "Verified OCR Receipt - {$this->editMerchantName} (Manually Corrected)",
        ]);

        // Recalculate HP for the linked budget cycle
        $cycle = BudgetCycle::find(
            ExpenseTransaction::where('receipt_id', $this->receipt->id)->value('budget_cycle_id')
        );

        if ($cycle) {
            $hpService->recalculateCycleHp($cycle);
        }

        $this->receipt->refresh()->load('items');
        $this->isEditing = false;
        $this->editItems = [];

        // Notify sibling components to refresh
        $this->dispatch('transactionRecorded');

        session()->flash('global_success', "✅ Struk \"{$this->editMerchantName}\" berhasil dikoreksi! Total: Rp " . number_format($this->editTotalAmount, 0, ',', '.'));
    }

    public function render()
    {
        return view('livewire.receipt-detail-modal');
    }
}
