<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\ConcreteMix;
use App\Models\InventoryItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = Order::with(['customer', 'concreteMix'])
            ->when($request->customer_id, fn($q,$v) => $q->where('customer_id',$v))
            ->when($request->status,      fn($q,$v) => $q->where('status',$v))
            ->when($request->type,        fn($q,$v) => $q->where('concrete_type',$v))
            ->when($request->from_date,   fn($q,$v) => $q->where('delivery_date','>=',$v))
            ->when($request->to_date,     fn($q,$v) => $q->where('delivery_date','<=',$v))
            ->latest('delivery_date')
            ->paginate(20)->withQueryString();

        $customers = Customer::active()->orderBy('name')->get();

        return view('orders.index', compact('orders','customers'));
    }

    public function create()
    {
        $customers    = Customer::active()->orderBy('name')->get();
        $concreteMixes = ConcreteMix::active()->orderBy('strength')->get();
        return view('orders.create', compact('customers','concreteMixes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'concrete_type'   => 'required|in:operational,complete',
            'concrete_mix_id' => 'required|exists:concrete_mixes,id',
            'quantity_m3'     => 'required|numeric|min:0.001',
            'location'        => 'nullable|string|max:255',
            'delivery_date'   => 'required|date',
            'delivery_time'   => 'nullable|date_format:H:i',
            'notes'           => 'nullable|string',
            'unit_price'      => 'nullable|numeric|min:0',
            'total_amount'    => 'nullable|numeric|min:0',
            'payment_type'    => 'nullable|in:cash,credit,mixed',
            'cash_amount'     => 'nullable|numeric|min:0',
            'credit_amount'   => 'nullable|numeric|min:0',
            'credit_due_date' => 'nullable|date',
            'material_prices' => 'nullable|array',
            'material_prices.*' => 'nullable|numeric|min:0',
            'material_quantities' => 'nullable|array',
            'material_quantities.*' => 'nullable|numeric|min:0',
            'expenses'        => 'nullable|array',
            'expenses.*.name' => 'required|string|max:255',
            'expenses.*.amount' => 'required|numeric|min:0',
            'expenses.*.notes' => 'nullable|string',
        ]);

        try {
            $order = $this->orderService->createOrder($validated);
            return redirect()->route('orders.show', $order)->with('success', 'تم إنشاء الطلب بنجاح');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'concreteMix', 'scheduleEntry', 'payments']);
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $customers     = Customer::active()->orderBy('name')->get();
        $concreteMixes = ConcreteMix::active()->orderBy('strength')->get();
        return view('orders.edit', compact('order','customers','concreteMixes'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'delivery_time' => 'nullable',
            'quantity_m3'   => 'required|numeric|min:0.001',
            'status'        => 'required|in:pending,scheduled,delivered,cancelled',
            'location'      => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
        ]);

        // If status is updated, we use the service to ensure weekly schedule entries are synchronized
        if ($order->status !== $validated['status']) {
            $this->orderService->updateOrderStatus($order, $validated['status']);
        }

        $order->update(collect($validated)->except('status')->toArray());

        // Sync with weekly schedule entry if it exists
        if ($order->scheduleEntry) {
            $order->scheduleEntry->update([
                'site_location' => $order->location ?? 'غير محدد',
                'quantity_m3'   => $order->quantity_m3,
                'delivery_date' => $order->delivery_date,
                'delivery_time' => $order->delivery_time,
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'تم تحديث الطلب');
    }

    public function destroy(Order $order)
    {
        try {
            $this->orderService->deleteOrder($order);
            return redirect()->route('orders.index')->with('success', 'تم حذف الطلب وجميع التكاليف والحركات المرتبطة به من الخزينة بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|in:pending,scheduled,delivered,cancelled']);
        $this->orderService->updateOrderStatus($order, $request->status);
        return back()->with('success', 'تم تحديث حالة الطلب');
    }

    /**
     * AJAX endpoint: return material quantities and costs for a mix + quantity.
     */
    public function mixCosts(Request $request)
    {
        $request->validate([
            'concrete_mix_id' => 'required|exists:concrete_mixes,id',
            'quantity_m3'     => 'required|numeric|min:0.001',
            'concrete_type'   => 'required|in:operational,complete',
        ]);

        $mix          = ConcreteMix::findOrFail($request->concrete_mix_id);
        $isOperational = $request->concrete_type === 'operational';
        $materials    = $this->orderService->calcMaterialCosts(
            (int)$mix->cement_per_m3,
            (float)$request->quantity_m3,
            $isOperational
        );

        return response()->json([
            'materials'  => $materials,
            'grand_total' => collect($materials)->sum('total'),
        ]);
    }
}
