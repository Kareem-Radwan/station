<?php

namespace App\Http\Controllers;

use App\Models\RentalMaintenance;
use App\Models\RentalContract;
use Illuminate\Http\Request;

class RentalMaintenanceController extends Controller
{
    public function create(RentalContract $rental)
    {
        return view('rentals.maintenance-create', compact('rental'));
    }

    public function store(Request $request, RentalContract $rental)
    {
        $request->validate([
            'maintenance_date' => 'required|date',
            'description'      => 'required|string',
            'cost'             => 'required|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        \DB::transaction(function () use ($request, $rental) {
            // Create maintenance record
            $maintenance = RentalMaintenance::create([
                'rental_contract_id' => $rental->id,
                'maintenance_date'   => $request->maintenance_date,
                'description'        => $request->description,
                'cost'               => $request->cost,
                'deducted_from_rent' => false,
                'notes'              => $request->notes,
                'recorded_by'        => auth()->id(),
            ]);

            // Record in treasury as expense using TreasuryService
            app(\App\Services\TreasuryService::class)->recordOutgoing(
                amount: (float) $request->cost,
                category: 'rental_maintenance',
                description: 'صيانة ' . $rental->equipment_name . ': ' . $request->description,
                referenceType: 'App\Models\RentalMaintenance',
                referenceId: $maintenance->id,
                transactionDate: $request->maintenance_date
            );
        });

        return redirect()->route('rentals.show', $rental)->with('success', 'تم تسجيل الصيانة وإضافتها للخزينة');
    }

    public function destroy(RentalMaintenance $maintenance)
    {
        $rentalId = $maintenance->rental_contract_id;
        
        \DB::transaction(function () use ($maintenance) {
            // Delete treasury transactions via TreasuryService
            app(\App\Services\TreasuryService::class)->deleteTransaction('App\Models\RentalMaintenance', $maintenance->id);
            
            $maintenance->delete();
        });
        
        return redirect()->route('rentals.show', $rentalId)->with('success', 'تم حذف سجل الصيانة وعكس معاملة الخزينة');
    }
}
