<?php

namespace App\Http\Controllers;

use App\Models\EquipmentFuelLog;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Services\TreasuryService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentFuelLogController extends Controller
{
    public function __construct(
        private TreasuryService $treasuryService,
        private InventoryService $inventoryService
    ) {}

    public function create(Equipment $equipment)
    {
        // Get all inventory items except concrete production materials
        $inventoryItems = InventoryItem::whereNotIn('name_ar', [
            'اسمنت', 'رمل', 'سن 1', 'سن 2', 'مادة', 'ماء'
        ])->orderBy('name_ar')->get();
        
        return view('equipment.fuel-log-create', compact('equipment', 'inventoryItems'));
    }

    public function store(Request $request, Equipment $equipment)
    {
        $request->validate([
            'log_date'  => 'required|date',
            'liters'    => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'hours_logged' => 'nullable|numeric|min:0',
            'days_logged' => 'nullable|integer|min:0',
            'notes'     => 'nullable|string',
            'deduct_from_inventory' => 'nullable|boolean',
            'inventory_item_id' => 'nullable|required_if:deduct_from_inventory,1|exists:inventory_items,id',
        ]);

        // Validate that the correct tracking field is provided
        if ($equipment->tracking_type === 'hours' && !$request->filled('hours_logged')) {
            return back()->withErrors(['hours_logged' => 'يجب إدخال عدد الساعات'])->withInput();
        }
        if ($equipment->tracking_type === 'days' && !$request->filled('days_logged')) {
            return back()->withErrors(['days_logged' => 'يجب إدخال عدد الأيام'])->withInput();
        }

        DB::transaction(function () use ($request, $equipment) {
            $totalCost = $request->liters * $request->unit_cost;
            $inventoryMovementId = null;
            $actualCost = $totalCost;

            // Calculate the increment (difference from current value)
            $incrementValue = 0;
            if ($equipment->tracking_type === 'hours') {
                $newValue = (float)$request->hours_logged;
                $incrementValue = $newValue - $equipment->current_hours;
                
                // Validate that new value is greater than current
                if ($incrementValue <= 0) {
                    throw new \Exception('القيمة الجديدة يجب أن تكون أكبر من القيمة الحالية (' . number_format($equipment->current_hours, 1) . ')');
                }
            } else {
                $newValue = (int)$request->days_logged;
                $incrementValue = $newValue - $equipment->current_days;
                
                // Validate that new value is greater than current
                if ($incrementValue <= 0) {
                    throw new \Exception('القيمة الجديدة يجب أن تكون أكبر من القيمة الحالية (' . number_format($equipment->current_days, 0) . ')');
                }
            }

            if ($request->deduct_from_inventory && $request->inventory_item_id) {
                $inventoryItem = InventoryItem::findOrFail($request->inventory_item_id);
                $actualCost = (float)$request->liters * (float)$inventoryItem->price_per_unit;
                
                $inventoryMovement = $this->inventoryService->stockOut(
                    itemId: (int)$request->inventory_item_id,
                    qty: (float)$request->liters,
                    meta: [
                        'reference_type' => 'equipment_fuel',
                        'reference_id' => null,
                        'notes' => 'خصم وقود لمعدة: ' . $equipment->name,
                        'date' => $request->log_date,
                        'price_per_unit' => (float)$inventoryItem->price_per_unit,
                    ]
                );
                $inventoryMovementId = $inventoryMovement->id;
            }

            // Store the increment value in the fuel log
            $fuelLog = EquipmentFuelLog::create([
                'equipment_id' => $equipment->id,
                'log_date'     => $request->log_date,
                'liters'       => $request->liters,
                'unit_cost'    => $request->unit_cost,
                'total_cost'   => $totalCost,
                'hours_logged' => $equipment->tracking_type === 'hours' ? $incrementValue : null,
                'days_logged'  => $equipment->tracking_type === 'days' ? $incrementValue : null,
                'deduct_from_inventory' => $request->deduct_from_inventory ?? false,
                'inventory_item_id' => $request->inventory_item_id,
                'inventory_movement_id' => $inventoryMovementId,
                'notes'        => $request->notes,
            ]);

            // Update equipment to the new value
            if ($equipment->tracking_type === 'hours') {
                $equipment->update(['current_hours' => $newValue]);
            } else {
                $equipment->update(['current_days' => $newValue]);
            }

            if ($actualCost > 0) {
                $description = $request->deduct_from_inventory 
                    ? 'وقود معدة من المخزون: ' . $equipment->name . ' (' . $request->liters . ' لتر)'
                    : 'وقود معدة: ' . $equipment->name . ' (' . $request->liters . ' لتر)';
                
                $this->treasuryService->recordOutgoing(
                    amount: $actualCost,
                    category: 'vehicle_equipment',
                    description: $description,
                    referenceType: 'equipment_fuel_log',
                    referenceId: $fuelLog->id
                );
            }
        });

        return redirect()->route('equipment.show', $equipment)->with('success', 'تم تسجيل الوقود بنجاح');
    }

    public function destroy(EquipmentFuelLog $fuelLog)
    {
        $equipmentId = $fuelLog->equipment_id;

        DB::transaction(function () use ($fuelLog) {
            $equipment = Equipment::find($fuelLog->equipment_id);
            
            // Reverse tracking values
            if ($equipment) {
                if ($equipment->tracking_type === 'hours' && $fuelLog->hours_logged) {
                    $equipment->decrement('current_hours', (float)$fuelLog->hours_logged);
                } elseif ($equipment->tracking_type === 'days' && $fuelLog->days_logged) {
                    $equipment->decrement('current_days', (int)$fuelLog->days_logged);
                }
            }
            
            // Delete treasury transaction if exists
            $this->treasuryService->deleteTransaction('equipment_fuel_log', $fuelLog->id);
            
            // Reverse inventory movement if exists
            if ($fuelLog->inventory_movement_id) {
                $movement = $fuelLog->inventoryMovement;
                if ($movement) {
                    $item = InventoryItem::lockForUpdate()->find($movement->inventory_item_id);
                    if ($item) {
                        $item->addStock($movement->quantity);
                    }
                    $movement->delete();
                }
            }
            
            $fuelLog->delete();
        });

        return redirect()->route('equipment.show', $equipmentId)->with('success', 'تم حذف السجل');
    }
}
