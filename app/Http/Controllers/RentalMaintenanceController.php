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

            // Record in treasury as expense
            $currentBalance = \App\Models\TreasuryTransaction::getCurrentBalance();
            $newBalance = $currentBalance - $request->cost;

            \App\Models\TreasuryTransaction::create([
                'type'             => 'out',
                'category'         => 'rental_maintenance',
                'amount'           => $request->cost,
                'balance_after'    => $newBalance,
                'transaction_date' => $request->maintenance_date,
                'description'      => 'صيانة ' . $rental->equipment_name . ': ' . $request->description,
                'reference_type'   => 'App\Models\RentalMaintenance',
                'reference_id'     => $maintenance->id,
                'recorded_by'      => auth()->id(),
            ]);
        });

        return redirect()->route('rentals.show', $rental)->with('success', 'تم تسجيل الصيانة وإضافتها للخزينة');
    }

    public function destroy(RentalMaintenance $maintenance)
    {
        $rentalId = $maintenance->rental_contract_id;
        
        \DB::transaction(function () use ($maintenance) {
            // Find and delete the related treasury transaction
            $treasuryTransaction = \App\Models\TreasuryTransaction::where('reference_type', 'App\Models\RentalMaintenance')
                ->where('reference_id', $maintenance->id)
                ->first();
            
            if ($treasuryTransaction) {
                // Recalculate all balances after this transaction
                $subsequentTransactions = \App\Models\TreasuryTransaction::where('id', '>', $treasuryTransaction->id)
                    ->orderBy('id')
                    ->get();
                
                // Delete the transaction
                $treasuryTransaction->delete();
                
                // Recalculate balances for all subsequent transactions
                if ($subsequentTransactions->isNotEmpty()) {
                    $currentBalance = \App\Models\TreasuryTransaction::where('id', '<', $treasuryTransaction->id)
                        ->latest('id')
                        ->value('balance_after') ?? 0;
                    
                    foreach ($subsequentTransactions as $trans) {
                        if ($trans->type === 'in') {
                            $currentBalance += $trans->amount;
                        } else {
                            $currentBalance -= $trans->amount;
                        }
                        $trans->update(['balance_after' => $currentBalance]);
                    }
                }
            }
            
            $maintenance->delete();
        });
        
        return redirect()->route('rentals.show', $rentalId)->with('success', 'تم حذف سجل الصيانة وعكس معاملة الخزينة');
    }
}
