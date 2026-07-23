<?php

namespace App\Http\Controllers;

use App\Models\EquipmentMaintenance;
use App\Models\Equipment;
use App\Models\Supplier;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentMaintenanceController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function create(Equipment $equipment)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('equipment.maintenance-create', compact('equipment','suppliers'));
    }

    public function store(Request $request, Equipment $equipment)
    {
        $request->validate([
            'maintenance_date' => 'required|date',
            'type'             => 'required|in:routine,repair,spare_part',
            'description'      => 'required|string',
            'cost'             => 'required|numeric|min:0',
            'hours_logged'     => 'nullable|numeric|min:0',
            'days_logged'      => 'nullable|integer|min:0',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'notes'            => 'nullable|string',
        ]);

        // Validate that the correct tracking field is provided
        if ($equipment->tracking_type === 'hours' && !$request->filled('hours_logged')) {
            return back()->withErrors(['hours_logged' => 'يجب إدخال عدد الساعات'])->withInput();
        }
        if ($equipment->tracking_type === 'days' && !$request->filled('days_logged')) {
            return back()->withErrors(['days_logged' => 'يجب إدخال عدد الأيام'])->withInput();
        }

        DB::transaction(function () use ($request, $equipment) {
            // Calculate the new value (user enters new total, not increment)
            if ($equipment->tracking_type === 'hours') {
                $newValue = (float)$request->hours_logged;
                
                // Validate that new value is greater than current
                if ($newValue <= $equipment->current_hours) {
                    throw new \Exception('القيمة الجديدة يجب أن تكون أكبر من القيمة الحالية (' . number_format($equipment->current_hours, 1) . ')');
                }
            } else {
                $newValue = (int)$request->days_logged;
                
                // Validate that new value is greater than current
                if ($newValue <= $equipment->current_days) {
                    throw new \Exception('القيمة الجديدة يجب أن تكون أكبر من القيمة الحالية (' . number_format($equipment->current_days, 0) . ')');
                }
            }

            $maintenance = EquipmentMaintenance::create(array_merge($request->all(), [
                'equipment_id' => $equipment->id,
                'hours_at_maintenance' => $equipment->tracking_type === 'hours' ? $newValue : null,
                'days_at_maintenance' => $equipment->tracking_type === 'days' ? $newValue : null,
            ]));

            // Update equipment to new value and set last maintenance value
            if ($equipment->tracking_type === 'hours') {
                $equipment->update([
                    'current_hours' => $newValue,
                    'last_maintenance_at_hours' => $newValue
                ]);
            } else {
                $equipment->update([
                    'current_days' => $newValue,
                    'last_maintenance_at_days' => $newValue
                ]);
            }

            if ($maintenance->cost > 0) {
                $this->treasuryService->recordOutgoing(
                    amount: (float)$maintenance->cost,
                    category: 'vehicle_equipment',
                    description: 'صيانة معدة: ' . $equipment->name . ' (' . $maintenance->type_label . ') - ' . $maintenance->description,
                    referenceType: 'equipment_maintenance',
                    referenceId: $maintenance->id
                );
            }
        });

        return redirect()->route('equipment.show', $equipment)->with('success', 'تم تسجيل الصيانة بنجاح');
    }

    public function destroy(EquipmentMaintenance $maintenance)
    {
        $equipmentId = $maintenance->equipment_id;

        DB::transaction(function () use ($maintenance) {
            $this->treasuryService->deleteTransaction('equipment_maintenance', $maintenance->id);
            $maintenance->delete();
        });

        return redirect()->route('equipment.show', $equipmentId)->with('success', 'تم حذف السجل');
    }
}
