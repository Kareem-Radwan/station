<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->when($request->search, fn($q,$v) => $q->where('name','like',"%$v%"))
            ->withCount('purchases')
            ->latest()->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'materials'    => 'nullable|array',
            'payment_type' => 'required|in:cash,credit,mixed',
            'notes'        => 'nullable|string',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد بنجاح');
    }

    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->with('items')->latest('purchase_date')->paginate(15, ['*'], 'purchases_page');
        $payments  = $supplier->payments()->latest('payment_date')->paginate(15, ['*'], 'payments_page');
        $totalPurchases = $supplier->purchases()->sum('total_amount');
        $totalPayments  = $supplier->payments()->sum('amount');
        return view('suppliers.show', compact('supplier','purchases','payments','totalPurchases','totalPayments'));
    }

    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'materials'    => 'nullable|array',
            'payment_type' => 'required|in:cash,credit,mixed',
            'notes'        => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'تم تحديث بيانات المورد');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->update(['is_active' => false]);
        return redirect()->route('suppliers.index')->with('success', 'تم إيقاف المورد');
    }
}
