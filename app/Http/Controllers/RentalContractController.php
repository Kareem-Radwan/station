<?php

namespace App\Http\Controllers;

use App\Models\RentalContract;
use App\Models\RentalMaintenance;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class RentalContractController extends Controller
{
    public function index()
    {
        $rentals = RentalContract::with('supplier')->latest()->paginate(20)->withQueryString();
        return view('rentals.index', compact('rentals'));
    }

    public function create()
    {
        $suppliers      = Supplier::active()->orderBy('name')->get();
        $gasItems       = InventoryItem::orderBy('name_ar')->get();
        return view('rentals.create', compact('suppliers', 'gasItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'equipment_name'   => 'required|string|max:255',
            'car_number'       => 'nullable|string|max:100',
            'driver_name'      => 'nullable|string|max:255',
            'hourly_price'     => 'nullable|numeric|min:0',
            'driver_allowance' => 'nullable|numeric|min:0',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'payment_type'     => 'required|in:cash,credit,mixed',
            'notes'            => 'nullable|string',
        ]);

        RentalContract::create($request->only([
            'equipment_name', 'car_number', 'driver_name',
            'hourly_price', 'driver_allowance',
            'supplier_id', 'payment_type', 'notes',
        ]));

        return redirect()->route('rentals.index')->with('success', 'تم إضافة السيارة المستأجرة بنجاح');
    }

    public function show(RentalContract $rental)
    {
        $rental->load(['supplier']);
        $shifts       = $rental->shifts()->with('fuelInventoryItem')->latest('shift_date')->paginate(20, ['*'], 'shifts_page');
        $maintenance  = $rental->maintenance()->latest('maintenance_date')->paginate(15, ['*'], 'maint_page');
        $totalMaint   = $rental->getTotalMaintenanceCost();
        $deducted     = $rental->getDeductedMaintenanceCost();
        $totalShifts  = $rental->getTotalShiftsCost();
        $totalFuel    = $rental->getTotalFuelCost();

        $gasItems = \App\Models\InventoryItem::orderBy('name_ar')->get();

        return view('rentals.show', compact(
            'rental', 'shifts', 'maintenance', 'totalMaint', 'deducted',
            'totalShifts', 'totalFuel', 'gasItems'
        ));
    }

    public function edit(RentalContract $rental)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('rentals.edit', compact('rental', 'suppliers'));
    }

    public function update(Request $request, RentalContract $rental)
    {
        $request->validate([
            'equipment_name'   => 'required|string|max:255',
            'car_number'       => 'nullable|string|max:100',
            'driver_name'      => 'nullable|string|max:255',
            'hourly_price'     => 'nullable|numeric|min:0',
            'driver_allowance' => 'nullable|numeric|min:0',
            'status'           => 'required|in:active,expired,cancelled',
            'notes'            => 'nullable|string',
        ]);

        $rental->update($request->only([
            'equipment_name', 'car_number', 'driver_name',
            'hourly_price', 'driver_allowance', 'status', 'notes',
        ]));

        return redirect()->route('rentals.show', $rental)->with('success', 'تم تحديث البيانات');
    }

    public function destroy(RentalContract $rental)
    {
        $rental->update(['status' => 'cancelled']);
        return redirect()->route('rentals.index')->with('success', 'تم إلغاء العقد');
    }
}
