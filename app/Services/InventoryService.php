<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private TreasuryService $treasuryService
    ) {}
    public function stockIn(int $itemId, float $qty, array $meta = []): InventoryMovement
    {
        return DB::transaction(function () use ($itemId, $qty, $meta) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);
            $item->addStock($qty);

            $movement = InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type'              => 'in',
                'quantity'          => $qty,
                'balance_after'     => $item->current_stock,
                'supplier_id'       => $meta['supplier_id'] ?? null,
                'unit_cost'         => $meta['unit_cost'] ?? null,
                'total_cost'        => isset($meta['unit_cost'])
                    ? ((float)$meta['unit_cost'] * $qty)
                    : null,
                'reference_type'    => $meta['reference_type'] ?? 'manual',
                'reference_id'      => $meta['reference_id'] ?? null,
                'notes'             => $meta['notes'] ?? null,
                'recorded_by'       => auth()->id(),
                'movement_date'     => $meta['date'] ?? now()->toDateString(),
            ]);

            $isManualMovement = ($meta['reference_type'] ?? 'manual') === 'manual';
            if ($isManualMovement && !empty($movement->total_cost) && $movement->total_cost > 0) {
                $this->treasuryService->recordOutgoing(
                    amount: (float)$movement->total_cost,
                    category: 'supplier_payment',
                    description: 'شراء مخزون: ' . $item->name_ar,
                    referenceType: 'inventory_movement',
                    referenceId: $movement->id,
                );
            }

            return $movement;
        });
    }

    public function stockOut(int $itemId, float $qty, array $meta = []): InventoryMovement
    {
        return DB::transaction(function () use ($itemId, $qty, $meta) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);

            if ((float)$item->current_stock < $qty) {
                throw new \Exception('الكمية في المخزن غير كافية. الرصيد الحالي: ' . $item->current_stock . ' ' . $item->unit);
            }

            $item->deductStock($qty);

            $unitPrice = (float)($meta['price_per_unit'] ?? 0);
            $totalSale = $unitPrice * $qty;

            $movement = InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type'              => 'out',
                'quantity'          => $qty,
                'balance_after'     => $item->current_stock,
                'unit_cost'         => $unitPrice,
                'total_cost'        => $totalSale,
                'reference_type'    => $meta['reference_type'] ?? 'manual',
                'reference_id'      => $meta['reference_id'] ?? null,
                'notes'             => $meta['notes'] ?? null,
                'recorded_by'       => auth()->id(),
                'movement_date'     => $meta['date'] ?? now()->toDateString(),
            ]);
    
            // Only create treasury transaction for sales (not for equipment/rental fuel or other deductions)
            $skipAutoTreasury = in_array($meta['reference_type'] ?? null, ['equipment_fuel', 'rental_shift_fuel']);
            if ($totalSale > 0 && !$skipAutoTreasury) {
                $this->treasuryService->recordIncoming(
                    amount: $totalSale,
                    category: 'customer_payment',
                    description: 'بيع مخزون: ' . $item->name_ar,
                    referenceType: 'inventory_movement',
                    referenceId: $movement->id,
                );
            }

            return $movement;
        });
    }

    public function getLowStockItems()
    {
        return InventoryItem::whereRaw('current_stock <= alert_threshold')->get();
    }

    public function deletePurchaseMovements(int $purchaseId): void
    {
        DB::transaction(function () use ($purchaseId) {
            $movements = InventoryMovement::where('reference_type', 'purchase')
                ->where('reference_id', $purchaseId)
                ->get();

            foreach ($movements as $m) {
                $item = InventoryItem::lockForUpdate()->find($m->inventory_item_id);
                if ($item) {
                    $item->deductStock($m->quantity);
                }
                $m->delete();
            }
        });
    }
}
