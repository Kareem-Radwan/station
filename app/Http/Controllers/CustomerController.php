<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
        }
        if ($request->type) $query->where('concrete_type', $request->type);
        if ($request->status === 'active')   $query->where('is_active', true);
        if ($request->status === 'inactive') $query->where('is_active', false);

        $customers = $query->withCount('orders')->latest()->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'concrete_type'    => 'required|in:operational,complete',
            'payment_type'     => 'required|in:cash,credit,mixed',
            'cement_balance'   => 'nullable|numeric|min:0',
            'invoice_number'   => 'nullable|string|max:100',
            'concrete_strength'=> 'nullable|integer|in:180,250,300',
            'cement_content'   => 'nullable|numeric|min:0',
        ]);

        // For complete customers, cement_balance should be 0
        if ($validated['concrete_type'] === 'complete') {
            $validated['cement_balance'] = 0;
        } else {
            // For operational customers, default to 0 if not provided
            $validated['cement_balance'] = $validated['cement_balance'] ?? 0;
        }

        $customer = Customer::create($validated);

        // If operational customer with cement balance, add to inventory
        if ($customer->isOperational() && !empty($validated['cement_balance']) && $validated['cement_balance'] > 0) {
            $this->addCementToInventory($customer, (float)$validated['cement_balance'], $validated['invoice_number'] ?? null);
        }

        return redirect()->route('customers.index')->with('success', 'تم إضافة العميل بنجاح');
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders.concreteMix', 'payments', 'credits']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'concrete_type'    => 'required|in:operational,complete',
            'payment_type'     => 'required|in:cash,credit,mixed',
            'concrete_strength'=> 'nullable|integer|in:180,250,300',
            'cement_content'   => 'nullable|numeric|min:0',
            'is_active'        => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)->with('success', 'تم تحديث بيانات العميل');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['is_active' => false]);
        return redirect()->route('customers.index')->with('success', 'تم إيقاف العميل');
    }

    public function addCement(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.001',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string'
        ]);

        if (!$customer->isOperational()) {
            return back()->with('error', 'هذا العميل ليس تشغيلياً');
        }

        $customer->addCement((float)$request->amount);
        $this->addCementToInventory($customer, (float)$request->amount, $request->invoice_number);

        return back()->with('success', 'تم إضافة ' . number_format($request->amount, 0) . ' طن إلى رصيد الاسمنت');
    }

    /**
     * Add cement to inventory when customer brings cement
     */
    private function addCementToInventory(Customer $customer, float $tons, ?string $invoiceNumber = null): void
    {
        $cementItem = InventoryItem::where('name', 'Cement')->first();
        
        if (!$cementItem) {
            return; // Skip if cement not configured in inventory
        }

        DB::transaction(function () use ($cementItem, $tons, $customer, $invoiceNumber) {
            $cementItem = InventoryItem::lockForUpdate()->find($cementItem->id);
            $cementItem->addStock($tons);

            InventoryMovement::create([
                'inventory_item_id' => $cementItem->id,
                'type'              => 'in',
                'quantity'          => $tons,
                'balance_after'     => $cementItem->current_stock,
                'unit_cost'         => null, // No cost since customer provided
                'total_cost'        => null,
                'supplier_id'       => null,
                'reference_type'    => 'customer',
                'reference_id'      => $customer->id,
                'invoice_number'    => $invoiceNumber,
                'notes'             => 'اسمنت من عميل: ' . $customer->name,
                'recorded_by'       => auth()->id(),
                'movement_date'     => now()->toDateString(),
            ]);
        });
    }

    public function payments(Customer $customer)
    {
        $payments = $customer->payments()->with('order')->latest()->paginate(20);
        return view('customers.payments', compact('customer', 'payments'));
    }
}
