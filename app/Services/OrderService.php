<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\ConcreteMix;
use App\Models\Credit;
use App\Models\InventoryItem;
use App\Models\MixRecipe;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Get recipe from database or fallback to default.
     */
    private function getRecipe(int $cementPerM3): array
    {
        $mixRecipe = MixRecipe::where('cement_per_m3', $cementPerM3)->first();
        
        if ($mixRecipe) {
            return $mixRecipe->getRecipeArray();
        }

        // Fallback to default 350 recipe if not found
        $default = MixRecipe::where('cement_per_m3', 350)->first();
        return $default ? $default->getRecipeArray() : $this->getDefaultRecipe();
    }

    /**
     * Hardcoded fallback if database is empty
     */
    private function getDefaultRecipe(): array
    {
        return [
            'Sand'      => 0.45,
            'Gravel1'   => 0.30,
            'Gravel2'   => 0.30,
            'Cement'    => 350,
            'Water'     => 0.2,
            'Additives' => 4.5,
        ];
    }

    public function __construct(private TreasuryService $treasuryService) {}

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::lockForUpdate()->findOrFail($data['customer_id']);

            // Both order types require a concrete mix
            $mix = null;
            if (!empty($data['concrete_mix_id'])) {
                $mix = ConcreteMix::findOrFail($data['concrete_mix_id']);
            }

            if ($data['concrete_type'] === 'operational') {
                if (!$mix) {
                    throw new \Exception('يجب اختيار خلطة الخرسانة للطلبات التشغيلية');
                }
                $cementKg = $mix->cement_per_m3 * $data['quantity_m3'];
                $cementTons = $cementKg / 1000;

                if ((float)$customer->cement_balance < $cementTons) {
                    throw new \Exception('رصيد الاسمنت غير كافٍ. الرصيد الحالي: ' . number_format($customer->cement_balance, 3) . ' طن، المطلوب: ' . number_format($cementTons, 3) . ' طن');
                }

                $data['cement_deducted'] = $cementTons;
                $customer->deductCement($cementTons);
            }

            // Auto-calculate credit_amount if not provided
            if (isset($data['total_amount']) && isset($data['cash_amount'])) {
                if (!isset($data['credit_amount']) || $data['credit_amount'] === null) {
                    $data['credit_amount'] = $data['total_amount'] - $data['cash_amount'];
                }
            }

            // Extract expenses before creating order
            $expenses = $data['expenses'] ?? [];
            unset($data['expenses']);

            $order = Order::create(array_merge($data, ['created_by' => auth()->id()]));

            // Create order expenses
            if (!empty($expenses)) {
                foreach ($expenses as $expense) {
                    \App\Models\OrderExpense::create([
                        'order_id' => $order->id,
                        'expense_name' => $expense['name'],
                        'amount' => $expense['amount'],
                        'notes' => $expense['notes'] ?? null,
                    ]);
                }
            }

            // Handle credit record
            if (in_array($data['payment_type'] ?? '', ['credit', 'mixed']) && !empty($data['credit_amount']) && $data['credit_amount'] > 0) {
                Credit::create([
                    'creditable_type' => 'customer',
                    'creditable_id'   => $customer->id,
                    'reference_type'  => 'order',
                    'reference_id'    => $order->id,
                    'amount'          => $data['credit_amount'],
                    'due_date'        => $data['credit_due_date'] ?? now()->addDays(30)->toDateString(),
                    'status'          => 'pending',
                    'created_by'      => auth()->id(),
                ]);
            }

            // DO NOT record cash or deduct inventory until order is delivered
            // This will be handled in updateOrderStatus() when status becomes 'delivered'

            return $order;
        });
    }

    /**
     * Deduct inventory stock and record material costs to treasury.
     *
     * For operational orders: deduct Cement from both customer balance AND inventory (customer brings cement to plant).
     * For complete orders: deduct all 6 materials including Cement from inventory.
     * 
     * Treasury cost deduction:
     * - Operational orders: all materials EXCEPT Cement (customer provides cement)
     * - Complete orders: ALL materials INCLUDING Cement (plant provides everything)
     * 
     * Uses custom material prices from order if available, otherwise falls back to current inventory prices.
     */
    public function deductInventoryForOrder(Order $order, ConcreteMix $mix, bool $isOperational): void
    {
        $cementPerM3 = (int)$mix->cement_per_m3;
        $recipe = $this->getRecipe($cementPerM3);
        $qty    = (float)$order->quantity_m3;
        $customPrices = $order->material_prices ?? [];

        foreach ($recipe as $materialName => $amountPerM3) {
            // Lock the row for update to prevent race conditions
            $item = InventoryItem::where('name', $materialName)->lockForUpdate()->first();
            if (!$item) {
                continue; // Skip if item not configured in inventory
            }

            // Convert cement from kg to tons
            $deductQty = ($materialName === 'Cement')
                ? ($amountPerM3 * $qty) / 1000
                : ($amountPerM3 * $qty);

            if ($deductQty <= 0) continue;

            // Deduct from inventory stock (for ALL materials including Cement)
            if ((float)$item->current_stock < $deductQty) {
                throw new \Exception(
                    'المخزون غير كافٍ لـ ' . $item->name_ar .
                        '. الرصيد الحالي: ' . number_format($item->current_stock, 3) . ' ' . $item->unit .
                        '، المطلوب: ' . number_format($deductQty, 3) . ' ' . $item->unit
                );
            }

            $item->deductStock($deductQty);

            \App\Models\InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'type'              => 'out',
                'quantity'          => $deductQty,
                'balance_after'     => $item->current_stock,
                'reference_type'    => 'order',
                'reference_id'      => $order->id,
                'notes'             => 'خصم تلقائي - طلب #' . $order->id,
                'recorded_by'       => auth()->id(),
                'movement_date'     => now()->toDateString(),
            ]);

            // Deduct material cost from treasury
            // For operational orders: skip Cement (customer provides it)
            // For complete orders: include Cement (plant provides everything)
            $shouldDeductCost = $isOperational ? ($materialName !== 'Cement') : true;
            
            if ($shouldDeductCost) {
                // Use custom price if provided, otherwise use current inventory price
                $pricePerUnit = isset($customPrices[$materialName]) && $customPrices[$materialName] > 0
                    ? (float)$customPrices[$materialName]
                    : (float)$item->price_per_unit;
                    
                $cost = $deductQty * $pricePerUnit;
                if ($cost > 0) {
                    $this->treasuryService->recordOutgoing(
                        amount: $cost,
                        category: 'material_cost',
                        description: 'تكلفة ' . $item->name_ar . ' - طلب #' . $order->id . ' (' . number_format($deductQty, 3) . ' ' . $item->unit . ' × ' . number_format($pricePerUnit, 2) . ' جنية)',
                        referenceType: 'order',
                        referenceId: $order->id
                    );
                }
            }
        }
    }

    /**
     * Refund all inventory deductions for a given order (rollback).
     */
    public function refundInventoryForOrder(Order $order): void
    {
        $movements = \App\Models\InventoryMovement::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'out')
            ->with('item')
            ->get();

        foreach ($movements as $movement) {
            $item = $movement->item;
            if (!$item) continue;
            $item->addStock((float)$movement->quantity);
            $movement->delete();
        }

        // Reverse treasury material cost transactions for this order
        $transactions = \App\Models\TreasuryTransaction::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('category', 'material_cost')
            ->get();

        foreach ($transactions as $tx) {
            // Add back the amount that was deducted
            $this->treasuryService->recordIncoming(
                amount: (float)$tx->amount,
                category: 'material_cost_refund',
                description: 'استرجاع تكلفة مواد - إلغاء طلب #' . $order->id,
                referenceType: 'order',
                referenceId: $order->id
            );
            $tx->delete();
        }
    }

    public function updateOrderStatus(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $oldStatus = $order->status;
            $order->update(['status' => $status]);

            if ($status === 'scheduled') {
                // Ensure there is a schedule entry
                if (!$order->scheduleEntry) {
                    $deliveryDate = $order->delivery_date;
                    // Find or create weekly schedule for this date range (Mon-Sun)
                    $startOfWeek = $deliveryDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                    $endOfWeek = $deliveryDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

                    $schedule = \App\Models\WeeklySchedule::firstOrCreate(
                        [
                            'week_start' => $startOfWeek->toDateString(),
                            'week_end'   => $endOfWeek->toDateString(),
                        ],
                        [
                            'status'     => 'draft',
                            'notes'      => 'تم إنشاؤه تلقائياً لجدولة الطلبات',
                        ]
                    );

                    \App\Models\WeeklyScheduleEntry::create([
                        'weekly_schedule_id' => $schedule->id,
                        'order_id'           => $order->id,
                        'customer_id'        => $order->customer_id,
                        'site_location'      => $order->location ?? 'غير محدد',
                        'quantity_m3'        => $order->quantity_m3,
                        'delivery_date'      => $order->delivery_date,
                        'delivery_time'      => $order->delivery_time,
                        'engineer_notes'     => $order->notes,
                        'status'             => 'pending',
                    ]);
                }
            } elseif ($status === 'cancelled') {
                if ($order->scheduleEntry) {
                    $order->scheduleEntry->update(['status' => 'cancelled']);
                }
                
                // If order was already delivered, we need to reverse the transactions
                if ($oldStatus === 'delivered') {
                    $this->reverseDeliveredOrder($order);
                }
            } elseif ($status === 'delivered') {
                if ($order->scheduleEntry) {
                    $order->scheduleEntry->update(['status' => 'completed']);
                }
                
                // NOW deduct inventory and record treasury transactions
                $this->processDeliveredOrder($order);
            } else {
                // If moved back to pending from delivered, reverse the deductions
                if ($oldStatus === 'delivered') {
                    $this->reverseDeliveredOrder($order);
                }
                
                // If moved back to pending, delete the schedule entry
                if ($order->scheduleEntry) {
                    $order->scheduleEntry->delete();
                }
            }

            return $order;
        });
    }

    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Restore cement balance if operational
            if ($order->concrete_type === 'operational' && $order->cement_deducted > 0) {
                $order->customer->addCement((float)$order->cement_deducted);
            }

            // If order was delivered, reverse inventory and treasury transactions
            if ($order->status === 'delivered') {
                $this->reverseDeliveredOrder($order);
            }

            $order->update(['status' => 'cancelled']);
        });
    }

    /**
     * Process order delivery: deduct inventory and record treasury transactions.
     * This is called ONLY when order status becomes 'delivered'.
     */
    private function processDeliveredOrder(Order $order): void
    {
        $order->load(['customer', 'concreteMix', 'expenses']);
        
        // Record cash payment in treasury
        if (!empty($order->cash_amount) && $order->cash_amount > 0) {
            $this->treasuryService->recordIncoming(
                amount: (float)$order->cash_amount,
                category: 'customer_payment',
                description: 'دفعة نقدية - طلب #' . $order->id . ' - ' . $order->customer->name,
                referenceType: 'order',
                referenceId: $order->id
            );
        }

        // Deduct inventory materials
        if ($order->concreteMix) {
            $this->deductInventoryForOrder(
                $order, 
                $order->concreteMix, 
                $order->concrete_type === 'operational'
            );
        }

        // Deduct order expenses from treasury
        foreach ($order->expenses as $expense) {
            $this->treasuryService->recordOutgoing(
                amount: (float)$expense->amount,
                category: 'order_expense',
                description: 'مصروف طلب: ' . $expense->expense_name . ' - طلب #' . $order->id,
                referenceType: 'order_expense',
                referenceId: $expense->id
            );
        }
    }

    /**
     * Reverse order delivery: refund inventory and reverse treasury transactions.
     * This is called when order status changes FROM 'delivered' to another status.
     */
    private function reverseDeliveredOrder(Order $order): void
    {
        // Restore inventory stock that was deducted
        $this->refundInventoryForOrder($order);

        // Reverse cash payment treasury transaction
        $cashTransaction = \App\Models\TreasuryTransaction::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('category', 'customer_payment')
            ->where('type', 'in')
            ->first();

        if ($cashTransaction) {
            $this->treasuryService->recordOutgoing(
                amount: (float)$cashTransaction->amount,
                category: 'customer_payment_refund',
                description: 'استرجاع دفعة نقدية - إلغاء/تعديل طلب #' . $order->id,
                referenceType: 'order',
                referenceId: $order->id
            );
            $cashTransaction->delete();
        }

        // Reverse order expense transactions
        $order->load('expenses');
        foreach ($order->expenses as $expense) {
            $expenseTransaction = \App\Models\TreasuryTransaction::where('reference_type', 'order_expense')
                ->where('reference_id', $expense->id)
                ->where('category', 'order_expense')
                ->where('type', 'out')
                ->first();

            if ($expenseTransaction) {
                $this->treasuryService->recordIncoming(
                    amount: (float)$expenseTransaction->amount,
                    category: 'order_expense_refund',
                    description: 'استرجاع مصروف: ' . $expense->expense_name . ' - إلغاء طلب #' . $order->id,
                    referenceType: 'order_expense',
                    referenceId: $expense->id
                );
                $expenseTransaction->delete();
            }
        }
    }

    /**
     * Calculate material quantities and costs for a given mix + quantity.
     * Returns array of materials with their quantities, prices, and totals.
     */
    public function calcMaterialCosts(int $mixCementPerM3, float $quantityM3, bool $isOperational): array
    {
        $recipe = $this->getRecipe($mixCementPerM3);
        $items  = InventoryItem::whereIn('name', array_keys($recipe))->get()->keyBy('name');

        $result = [];
        foreach ($recipe as $materialName => $amountPerM3) {
            if ($isOperational && $materialName === 'Cement') {
                continue; // Cement not shown for operational (handled separately)
            }

            $item = $items->get($materialName);
            $qty  = ($materialName === 'Cement')
                ? ($amountPerM3 * $quantityM3) / 1000
                : ($amountPerM3 * $quantityM3);

            $unit       = $item?->unit ?? ($materialName === 'Cement' ? 'طن' : ($materialName === 'Additives' ? 'لتر' : 'م³'));
            $price      = (float)($item?->price_per_unit ?? 0);
            $total      = $qty * $price;

            $result[] = [
                'name'         => $materialName,
                'name_ar'      => $item?->name_ar ?? $materialName,
                'unit'         => $unit,
                'quantity'     => round($qty, 3),
                'price_per_unit' => $price,
                'total'        => round($total, 2),
                'in_stock'     => (float)($item?->current_stock ?? 0),
                'is_cement'    => $materialName === 'Cement',
            ];
        }

        return $result;
    }
}
