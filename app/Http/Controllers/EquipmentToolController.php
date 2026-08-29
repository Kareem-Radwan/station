<?php

namespace App\Http\Controllers;

use App\Models\EquipmentTool;
use App\Models\EquipmentToolMovement;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentToolController extends Controller
{
    public function index()
    {
        $tools = EquipmentTool::withSum('movements', 'total_cost')
            ->orderBy('name')
            ->paginate(15);

        return view('equipment-tools.index', compact('tools'));
    }

    public function create()
    {
        return view('equipment-tools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:100',
            'quantity' => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['price_per_unit'] = $validated['price_per_unit'] ?? 0;
        $validated['total_value'] = $validated['quantity'] * $validated['price_per_unit'];

        DB::transaction(function () use ($validated) {
            $tool = EquipmentTool::create($validated);

            // If there's initial quantity, create treasury transaction and movement
            if ($validated['quantity'] > 0 && $validated['total_value'] > 0) {
                // Create treasury transaction (expense) using TreasuryService
                $treasuryService = app(\App\Services\TreasuryService::class);
                $treasuryTransaction = $treasuryService->recordOutgoing(
                    amount: (float) $validated['total_value'],
                    category: 'مشتريات مخزون المعدات',
                    description: "رصيد افتتاحي: {$validated['quantity']} {$validated['unit']} من {$validated['name']}",
                    referenceType: EquipmentTool::class,
                    referenceId: $tool->id,
                    transactionDate: now()->toDateString()
                );

                // Create initial movement record
                EquipmentToolMovement::create([
                    'equipment_tool_id' => $tool->id,
                    'type' => 'in',
                    'quantity' => $validated['quantity'],
                    'price_per_unit' => $validated['price_per_unit'],
                    'total_cost' => $validated['total_value'],
                    'balance_after' => $validated['quantity'],
                    'treasury_transaction_id' => $treasuryTransaction->id,
                    'notes' => 'رصيد افتتاحي',
                    'movement_date' => now(),
                ]);
            }
        });

        return redirect()
            ->route('equipment-tools.index')
            ->with('success', 'تم إضافة الأداة بنجاح');
    }

    public function show(EquipmentTool $equipmentTool)
    {
        $movements = $equipmentTool->movements()
            ->with('treasuryTransaction')
            ->latest('movement_date')
            ->paginate(20);

        return view('equipment-tools.show', compact('equipmentTool', 'movements'));
    }

    public function stockInForm(EquipmentTool $equipmentTool)
    {
        return view('equipment-tools.stock-in', compact('equipmentTool'));
    }

    public function stockIn(Request $request, EquipmentTool $equipmentTool)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'total_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'movement_date' => 'required|date',
        ]);

        DB::transaction(function () use ($equipmentTool, $validated) {
            $quantity = $validated['quantity'];
            $totalCost = $validated['total_cost'];
            $pricePerUnit = $totalCost / $quantity;

            // حساب السعر الجديد للوحدة (متوسط السعر المرجح)
            $oldQuantity = $equipmentTool->quantity;
            $oldValue = $equipmentTool->total_value;
            $newQuantity = $oldQuantity + $quantity;
            $newValue = $oldValue + $totalCost;
            $newPricePerUnit = $newQuantity > 0 ? $newValue / $newQuantity : 0;

            // تحديث الأداة
            $equipmentTool->quantity = $newQuantity;
            $equipmentTool->price_per_unit = $newPricePerUnit;
            $equipmentTool->total_value = $newValue;
            $equipmentTool->save();

            // إنشاء معاملة خزينة (صرف) using TreasuryService
            $treasuryService = app(\App\Services\TreasuryService::class);
            $treasuryTransaction = $treasuryService->recordOutgoing(
                amount: (float) $totalCost,
                category: 'مشتريات مخزون المعدات',
                description: "شراء {$quantity} {$equipmentTool->unit} من {$equipmentTool->name}",
                referenceType: EquipmentTool::class,
                referenceId: $equipmentTool->id,
                transactionDate: $validated['movement_date']
            );

            // تسجيل الحركة
            EquipmentToolMovement::create([
                'equipment_tool_id' => $equipmentTool->id,
                'type' => 'in',
                'quantity' => $quantity,
                'price_per_unit' => $pricePerUnit,
                'total_cost' => $totalCost,
                'balance_after' => $newQuantity,
                'treasury_transaction_id' => $treasuryTransaction->id,
                'notes' => $validated['notes'],
                'movement_date' => $validated['movement_date'],
            ]);
        });

        return redirect()
            ->route('equipment-tools.show', $equipmentTool)
            ->with('success', 'تم إضافة الكمية بنجاح');
    }

    public function stockOutForm(EquipmentTool $equipmentTool)
    {
        return view('equipment-tools.stock-out', compact('equipmentTool'));
    }

    public function stockOut(Request $request, EquipmentTool $equipmentTool)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'movement_date' => 'required|date',
        ]);

        if ($validated['quantity'] > $equipmentTool->quantity) {
            return back()->withErrors(['quantity' => 'الكمية المطلوبة أكبر من الكمية المتاحة']);
        }

        DB::transaction(function () use ($equipmentTool, $validated) {
            $quantity = $validated['quantity'];
            $pricePerUnit = $equipmentTool->price_per_unit;
            $totalCost = $quantity * $pricePerUnit;

            // تحديث الأداة
            $oldQuantity = $equipmentTool->quantity;
            $newQuantity = $oldQuantity - $quantity;
            $newValue = $newQuantity * $pricePerUnit;

            $equipmentTool->quantity = $newQuantity;
            $equipmentTool->total_value = $newValue;
            $equipmentTool->save();

            // Note: Stock-out is an expense (consumption), so it should be 'out' not 'in'
            // The treasury loses money when equipment is consumed
            $treasuryService = app(\App\Services\TreasuryService::class);
            $treasuryTransaction = $treasuryService->recordOutgoing(
                amount: (float) $totalCost,
                category: 'استهلاك مخزون المعدات',
                description: "استهلاك {$quantity} {$equipmentTool->unit} من {$equipmentTool->name}",
                referenceType: EquipmentTool::class,
                referenceId: $equipmentTool->id,
                transactionDate: $validated['movement_date']
            );

            // تسجيل الحركة
            EquipmentToolMovement::create([
                'equipment_tool_id' => $equipmentTool->id,
                'type' => 'out',
                'quantity' => $quantity,
                'price_per_unit' => $pricePerUnit,
                'total_cost' => $totalCost,
                'balance_after' => $newQuantity,
                'treasury_transaction_id' => $treasuryTransaction->id,
                'notes' => $validated['notes'],
                'movement_date' => $validated['movement_date'],
            ]);
        });

        return redirect()
            ->route('equipment-tools.show', $equipmentTool)
            ->with('success', 'تم خصم الكمية بنجاح');
    }
}
