<?php

namespace App\Http\Controllers;

use App\Models\NeighboringStation;
use App\Models\NeighboringStationTransaction;
use App\Services\NeighboringStationService;
use Illuminate\Http\Request;

class NeighboringStationController extends Controller
{
    public function __construct(private NeighboringStationService $service) {}

    public function index()
    {
        $stations = NeighboringStation::withCount('transactions')->paginate(20);
        return view('neighboring-stations.index', compact('stations'));
    }

    public function create()
    {
        return view('neighboring-stations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $station = NeighboringStation::create($validated);

        return redirect()->route('neighboring-stations.index')
            ->with('success', 'تم إضافة المحطة المجاورة بنجاح');
    }

    public function show(NeighboringStation $neighboringStation)
    {
        $transactions = $neighboringStation->transactions()
            ->with('recordedBy')
            ->orderBy('transaction_date', 'desc')
            ->paginate(25);

        return view('neighboring-stations.show', compact('neighboringStation', 'transactions'));
    }

    public function edit(NeighboringStation $neighboringStation)
    {
        return view('neighboring-stations.edit', compact('neighboringStation'));
    }

    public function update(Request $request, NeighboringStation $neighboringStation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $neighboringStation->update($validated);

        return redirect()->route('neighboring-stations.index')
            ->with('success', 'تم تحديث بيانات المحطة بنجاح');
    }

    public function destroy(NeighboringStation $neighboringStation)
    {
        if ($neighboringStation->transactions()->exists()) {
            return back()->with('error', 'لا يمكن حذف المحطة لوجود معاملات مرتبطة بها');
        }

        $neighboringStation->delete();

        return redirect()->route('neighboring-stations.index')
            ->with('success', 'تم حذف المحطة بنجاح');
    }

    // Transaction Management
    public function createTransaction(NeighboringStation $neighboringStation)
    {
        return view('neighboring-stations.create-transaction', compact('neighboringStation'));
    }

    public function storeTransaction(Request $request, NeighboringStation $neighboringStation)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:rent_equipment,rent_vehicle,borrow_material,borrow_inventory,sell_concrete,service',
            'direction' => 'required|in:incoming,outgoing',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'payment_status' => 'required|in:paid,pending,partial',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $validated['neighboring_station_id'] = $neighboringStation->id;

        $this->service->recordTransaction($validated);

        return redirect()->route('neighboring-stations.show', $neighboringStation)
            ->with('success', 'تم تسجيل المعاملة بنجاح');
    }

    public function editTransaction(NeighboringStation $neighboringStation, NeighboringStationTransaction $transaction)
    {
        return view('neighboring-stations.edit-transaction', compact('neighboringStation', 'transaction'));
    }

    public function updateTransaction(Request $request, NeighboringStation $neighboringStation, NeighboringStationTransaction $transaction)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:rent_equipment,rent_vehicle,borrow_material,borrow_inventory,sell_concrete,service',
            'direction' => 'required|in:incoming,outgoing',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'payment_status' => 'required|in:paid,pending,partial',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $this->service->updateTransaction($transaction, $validated);

        return redirect()->route('neighboring-stations.show', $neighboringStation)
            ->with('success', 'تم تحديث المعاملة بنجاح');
    }

    public function destroyTransaction(NeighboringStation $neighboringStation, NeighboringStationTransaction $transaction)
    {
        $this->service->deleteTransaction($transaction);

        return redirect()->route('neighboring-stations.show', $neighboringStation)
            ->with('success', 'تم حذف المعاملة بنجاح');
    }

    public function recordPayment(Request $request, NeighboringStation $neighboringStation, NeighboringStationTransaction $transaction)
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $this->service->recordPayment($transaction, $validated['payment_amount']);
            return back()->with('success', 'تم تسجيل الدفعة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
