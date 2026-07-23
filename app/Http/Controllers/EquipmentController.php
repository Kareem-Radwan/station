<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentFuelLog;
use App\Models\EquipmentMaintenance;
use App\Models\Supplier;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function index()
    {
        $equipment = Equipment::withSum('fuelLogs','total_cost')
            ->withSum('maintenance','cost')
            ->paginate(20);
        return view('equipment.index', compact('equipment'));
    }

    public function create() { return view('equipment.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:loader,mixer,service_vehicle,pump,generator',
            'model'         => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'tracking_type' => 'required|in:hours,days',
            'maintenance_threshold' => 'nullable|integer|min:1',
            'notes'         => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $equipment = Equipment::create($request->all());

            if ($equipment->purchase_cost > 0) {
                $this->treasuryService->recordOutgoing(
                    amount: (float)$equipment->purchase_cost,
                    category: 'vehicle_equipment',
                    description: 'شراء معدة: ' . $equipment->name . ' (' . $equipment->type_label . ')',
                    referenceType: 'equipment',
                    referenceId: $equipment->id
                );
            }
        });

        return redirect()->route('equipment.index')->with('success', 'تم إضافة المعدة بنجاح');
    }

    public function show(Equipment $equipment)
    {
        $fuelLogs    = $equipment->fuelLogs()->with('inventoryItem')->latest('log_date')->paginate(10, ['*'], 'fuel_page');
        $maintenance = $equipment->maintenance()->latest('maintenance_date')->paginate(10, ['*'], 'maint_page');
        $totalFuel   = $equipment->getTotalFuelCost();
        $totalMaint  = $equipment->getTotalMaintenanceCost();
        return view('equipment.show', compact('equipment','fuelLogs','maintenance','totalFuel','totalMaint'));
    }

    public function edit(Equipment $equipment) { return view('equipment.edit', compact('equipment')); }

    public function update(Request $request, Equipment $equipment)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:active,maintenance,inactive',
            'tracking_type' => 'required|in:hours,days',
            'maintenance_threshold' => 'nullable|integer|min:1',
            'notes'  => 'nullable|string',
        ]);
        $equipment->update($request->validated());
        return redirect()->route('equipment.show', $equipment)->with('success', 'تم تحديث المعدة');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->update(['status' => 'inactive']);
        return redirect()->route('equipment.index')->with('success', 'تم إيقاف المعدة');
    }
}
