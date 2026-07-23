<?php

namespace App\Http\Controllers;

use App\Models\RentalContract;
use App\Models\RentalShift;
use App\Models\InventoryItem;
use App\Services\TreasuryService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalShiftController extends Controller
{
    public function __construct(
        private TreasuryService $treasuryService,
        private InventoryService $inventoryService
    ) {}

    public function store(Request $request, RentalContract $rental)
    {
        $request->validate([
            'shift_date'        => 'required|date',
            'hours'             => 'required|numeric|min:0',
            'hourly_price'      => 'required|numeric|min:0',
            'gratuities'        => 'nullable|numeric|min:0',
            'cards_cost'        => 'nullable|numeric|min:0',
            'driver_allowance'  => 'nullable|numeric|min:0',
            'fuel_liters'       => 'nullable|numeric|min:0',
            'fuel_item_id'      => 'nullable|required_with:fuel_liters|exists:inventory_items,id',
            'notes'             => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $rental) {
            $hours         = (float)$request->hours;
            $hourlyPrice   = (float)$request->hourly_price;
            $gratuities    = (float)($request->gratuities ?? 0);
            $cardsCost     = (float)($request->cards_cost ?? 0);
            $driverAllow   = (float)($request->driver_allowance ?? 0);

            $hoursCost     = $hours * $hourlyPrice;
            $fuelCost      = 0;
            $fuelMovementId = null;

            // ── Fuel deduction from inventory ──────────────────────────────
            if ($request->filled('fuel_liters') && $request->filled('fuel_item_id')) {
                $fuelLiters = (float)$request->fuel_liters;
                $fuelItem = InventoryItem::findOrFail($request->fuel_item_id);
                $fuelCost = $fuelLiters * (float)$fuelItem->price_per_unit;

                $fuelMovement = $this->inventoryService->stockOut(
                    itemId: (int)$request->fuel_item_id,
                    qty: $fuelLiters,
                    meta: [
                        'reference_type' => 'rental_shift_fuel',
                        'reference_id'   => null,
                        'notes'          => 'وقود (گاز) سيارة مستأجرة: ' . $rental->equipment_name . ' (' . ($rental->car_number ?? '') . ')',
                        'date'           => $request->shift_date,
                        'price_per_unit' => (float)$fuelItem->price_per_unit,
                    ]
                );
                $fuelMovementId = $fuelMovement->id;

                // Treasury out for fuel cost
                $this->treasuryService->recordOutgoing(
                    amount: $fuelCost,
                    category: 'rental',
                    description: 'وقود وردية - ' . $rental->equipment_name . ' (' . ($rental->car_number ?? '') . ') ' . $fuelLiters . ' لتر',
                    referenceType: 'rental_shift_fuel',
                    referenceId: null,
                );
            }

            $totalCost = $hoursCost + $gratuities + $cardsCost + $driverAllow + $fuelCost;

            // ── Create the shift record ────────────────────────────────────
            $shift = RentalShift::create([
                'rental_contract_id'       => $rental->id,
                'shift_date'               => $request->shift_date,
                'hours'                    => $hours,
                'hourly_price'             => $hourlyPrice,
                'hours_cost'               => $hoursCost,
                'gratuities'               => $gratuities,
                'cards_cost'               => $cardsCost,
                'driver_allowance'         => $driverAllow,
                'total_cost'               => $totalCost,
                'fuel_liters'              => $request->filled('fuel_liters') ? (float)$request->fuel_liters : null,
                'fuel_inventory_item_id'   => $request->filled('fuel_item_id') ? (int)$request->fuel_item_id : null,
                'fuel_inventory_movement_id' => $fuelMovementId,
                'fuel_cost'                => $fuelCost > 0 ? $fuelCost : null,
                'notes'                    => $request->notes,
                'recorded_by'              => auth()->id(),
            ]);

            // ── Treasury out for shift costs (hours + gratuities + cards + allowance) ──
            $shiftPayCost = $hoursCost + $gratuities + $cardsCost + $driverAllow;
            if ($shiftPayCost > 0) {
                $breakdown = [];
                if ($hoursCost > 0)    $breakdown[] = 'ساعات: ' . number_format($hoursCost, 0);
                if ($gratuities > 0)   $breakdown[] = 'اكراميات: ' . number_format($gratuities, 0);
                if ($cardsCost > 0)    $breakdown[] = 'كارتات: ' . number_format($cardsCost, 0);
                if ($driverAllow > 0)  $breakdown[] = 'معيشة: ' . number_format($driverAllow, 0);

                $this->treasuryService->recordOutgoing(
                    amount: $shiftPayCost,
                    category: 'rental',
                    description: 'وردية - ' . $rental->equipment_name . ' (' . ($rental->car_number ?? '') . ') ' . $request->shift_date . ' [' . implode(', ', $breakdown) . ']',
                    referenceType: 'rental_shift',
                    referenceId: $shift->id,
                );
            }
        });

        return redirect()->route('rentals.show', $rental)->with('success', 'تم تسجيل الوردية بنجاح');
    }

    public function destroy(RentalShift $shift)
    {
        $rentalId = $shift->rental_contract_id;

        DB::transaction(function () use ($shift) {
            // Reverse treasury transaction for shift pay
            $this->treasuryService->deleteTransaction('rental_shift', $shift->id);

            // Reverse fuel inventory & treasury if applicable
            if ($shift->fuel_inventory_movement_id) {
                $movement = $shift->fuelInventoryMovement;
                if ($movement) {
                    $item = InventoryItem::lockForUpdate()->find($movement->inventory_item_id);
                    if ($item) {
                        $item->addStock($movement->quantity);
                    }
                    $movement->delete();
                }
            }

            $shift->delete();
        });

        return redirect()->route('rentals.show', $rentalId)->with('success', 'تم حذف الوردية');
    }
}
