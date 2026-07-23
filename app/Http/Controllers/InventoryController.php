<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $items = InventoryItem::query()
            ->when($request->search, fn($q, $v) => $q->where('name_ar', 'like', "%$v%")->orWhere('name', 'like', "%$v%"))
            ->orderBy('name_ar')
            ->paginate(15)
            ->withQueryString();
        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:inventory_items,name',
            'name_ar'         => 'required|string|max:100',
            'unit'            => 'required|string|max:20',
            'alert_threshold' => 'required|numeric|min:0',
            'current_stock'   => 'required|numeric|min:0',
            'price_per_unit'  => 'nullable|numeric|min:0',
        ]);

        InventoryItem::create($request->only(['name', 'name_ar', 'unit', 'alert_threshold', 'current_stock', 'price_per_unit']));

        return redirect()->route('inventory.index')->with('success', 'تم إضافة المادة بنجاح');
    }

    public function show(InventoryItem $inventory)
    {
        $movements = InventoryMovement::where('inventory_item_id', $inventory->id)
            ->with('supplier')
            ->latest('movement_date')
            ->paginate(25);
        return view('inventory.show', compact('inventory', 'movements'));
    }

    public function movements(InventoryItem $item)
    {
        $movements = InventoryMovement::where('inventory_item_id', $item->id)
            ->with('supplier')
            ->latest('movement_date')
            ->paginate(30);
        return view('inventory.movements', compact('item', 'movements'));
    }

    public function stockInForm(InventoryItem $item)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('inventory.stock-in', compact('item', 'suppliers'));
    }

    public function stockIn(Request $request, InventoryItem $item)
    {
        $request->validate([
            'quantity'    => 'required|numeric|min:0.001',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_cost'   => 'required|numeric|min:0',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        $this->inventoryService->stockIn($item->id, (float)$request->quantity, $request->except('_token'));

        return redirect()->route('inventory.index')->with('success', 'تم إضافة المخزون بنجاح');
    }

    public function stockOutForm(InventoryItem $item)
    {
        return view('inventory.stock-out', compact('item'));
    }

    public function stockOut(Request $request, InventoryItem $item)
    {
        $request->validate([
            'quantity'   => 'required|numeric|min:0.001',
            'price_per_unit' => 'required|numeric|min:0',
            'date'       => 'required|date',
            'notes'      => 'nullable|string',
        ]);

        try {
            $this->inventoryService->stockOut($item->id, (float)$request->quantity, $request->except('_token'));
            return redirect()->route('inventory.index')->with('success', 'تم خصم المخزون بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updatePriceForm(InventoryItem $item)
    {
        return view('inventory.update-price', compact('item'));
    }

    public function updatePrice(Request $request, InventoryItem $item)
    {
        $request->validate([
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        $item->update(['price_per_unit' => $request->price_per_unit]);

        return redirect()->route('inventory.index')->with('success', 'تم تحديث السعر بنجاح');
    }
}
