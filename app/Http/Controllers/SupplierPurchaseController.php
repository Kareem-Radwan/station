<?php

namespace App\Http\Controllers;

use App\Models\SupplierPurchase;
use App\Models\SupplierPurchaseItem;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\Credit;
use App\Services\TreasuryService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPurchaseController extends Controller
{
    public function __construct(
        private TreasuryService $treasuryService,
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $purchases = SupplierPurchase::with('supplier')
            ->when($request->supplier_id, fn($q, $v) => $q->where('supplier_id', $v))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->latest('purchase_date')->paginate(20)->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();
        return view('supplier-purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers      = Supplier::active()->orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name_ar')->get();
        return view('supplier-purchases.create', compact('suppliers', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:100',
            'purchase_date'  => 'required|date',
            'payment_type'   => 'required|in:cash,credit,mixed',
            'cash_amount'    => 'nullable|numeric|min:0',
            'credit_amount'  => 'nullable|numeric|min:0',
            'due_date'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'invoice_image'  => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'items'          => 'required|array|min:1',
            'items.*.description'       => 'required|string',
            'items.*.quantity'          => 'required|numeric|min:0.001',
            'items.*.unit'              => 'required|string',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
        ]);

        // Handle optional invoice image upload
        $invoiceImagePath = null;
        if ($request->hasFile('invoice_image')) {
            $invoiceImagePath = $request->file('invoice_image')->store('purchases/invoices', 'public');
        }

        DB::transaction(function () use ($request, $invoiceImagePath) {
            $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            // Auto-calculate credit_amount if not provided
            $cashAmount = $request->cash_amount ?? 0;
            $creditAmount = $request->credit_amount ?? ($totalAmount - $cashAmount);

            $purchase = SupplierPurchase::create([
                'supplier_id'         => $request->supplier_id,
                'invoice_number'      => $request->invoice_number,
                'purchase_date'       => $request->purchase_date,
                'total_amount'        => $totalAmount,
                'payment_type'        => $request->payment_type,
                'cash_amount'         => $cashAmount,
                'credit_amount'       => $creditAmount,
                'due_date'            => $request->due_date,
                'status'              => 'pending',
                'notes'               => $request->notes,
                'invoice_image_path'  => $invoiceImagePath,
                'created_by'          => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                SupplierPurchaseItem::create([
                    'supplier_purchase_id' => $purchase->id,
                    'inventory_item_id'    => $item['inventory_item_id'] ?? null,
                    'description'          => $item['description'],
                    'quantity'             => $item['quantity'],
                    'unit'                 => $item['unit'],
                    'unit_price'           => $item['unit_price'],
                    'total_price'          => $totalPrice,
                ]);

                // Auto stock-in if linked to inventory item
                if (!empty($item['inventory_item_id'])) {
                    $this->inventoryService->stockIn((int)$item['inventory_item_id'], (float)$item['quantity'], [
                        'supplier_id'    => $request->supplier_id,
                        'unit_cost'      => $item['unit_price'],
                        'reference_type' => 'purchase',
                        'reference_id'   => $purchase->id,
                        'date'           => $request->purchase_date,
                    ]);
                }
            }

            // Update supplier balance (only add the credit portion)
            $purchase->supplier->increment('balance', $creditAmount);

            // Cash out of treasury
            if ($request->payment_type !== 'credit' && $cashAmount > 0) {
                // Build items description for treasury transaction
                $itemsDesc = collect($request->items)
                    ->map(fn($item) => $item['description'])
                    ->take(3)
                    ->implode('، ');
                
                if (count($request->items) > 3) {
                    $itemsDesc .= '، وأخرى';
                }
                
                $description = sprintf(
                    'فاتورة %s - مشتريات من %s (%s)',
                    $request->invoice_number ?? '#' . $purchase->id,
                    $purchase->supplier->name,
                    $itemsDesc
                );
                
                $this->treasuryService->recordOutgoing(
                    amount: (float)$cashAmount,
                    category: 'supplier_payment',
                    description: $description,
                    referenceType: 'purchase',
                    referenceId: $purchase->id,
                    transactionDate: $request->purchase_date
                );
            }

            // Create credit record
            if (in_array($request->payment_type, ['credit', 'mixed']) && $creditAmount > 0) {
                Credit::create([
                    'creditable_type' => 'supplier',
                    'creditable_id'   => $request->supplier_id,
                    'reference_type'  => 'purchase',
                    'reference_id'    => $purchase->id,
                    'amount'          => $creditAmount,
                    'due_date'        => $request->due_date ?? now()->addDays(30)->toDateString(),
                    'status'          => 'pending',
                    'created_by'      => auth()->id(),
                ]);
            }
        });

        return redirect()->route('supplier-purchases.index')->with('success', 'تم تسجيل المشتريات بنجاح');
    }

    public function show(SupplierPurchase $supplierPurchase)
    {
        $supplierPurchase->load(['supplier', 'items.inventoryItem', 'payments']);
        return view('supplier-purchases.show', compact('supplierPurchase'));
    }

    public function edit(SupplierPurchase $supplierPurchase)
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name_ar')->get();
        return view('supplier-purchases.edit', compact('supplierPurchase', 'suppliers', 'inventoryItems'));
    }

    public function update(Request $request, SupplierPurchase $supplierPurchase)
    {
        $request->validate(['notes' => 'nullable|string', 'status' => 'required|in:pending,partial,paid']);
        $supplierPurchase->update($request->only(['status', 'notes']));
        return redirect()->route('supplier-purchases.show', $supplierPurchase)->with('success', 'تم تحديث الفاتورة');
    }

    public function destroy(SupplierPurchase $supplierPurchase)
    {
        DB::transaction(function () use ($supplierPurchase) {
            // Revert supplier balance
            if ($supplierPurchase->supplier) {
                $supplierPurchase->supplier->decrement('balance', $supplierPurchase->total_amount);
            }

            // Delete credit records
            Credit::where('reference_type', 'purchase')
                ->where('reference_id', $supplierPurchase->id)
                ->delete();

            // Delete treasury transactions and recalculate balance
            $this->treasuryService->deleteTransaction('purchase', $supplierPurchase->id);

            // Delete inventory movements and adjust inventory stock
            $this->inventoryService->deletePurchaseMovements($supplierPurchase->id);

            // Delete the invoice image if it exists
            if (
                $supplierPurchase->invoice_image_path &&
                \Illuminate\Support\Facades\Storage::disk('public')->exists($supplierPurchase->invoice_image_path)
            ) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($supplierPurchase->invoice_image_path);
            }

            // Delete the purchase items
            $supplierPurchase->items()->delete();

            // Delete the purchase itself
            $supplierPurchase->delete();
        });

        return redirect()->route('supplier-purchases.index')->with('success', 'تم حذف الفاتورة');
    }

    /**
     * Upload or replace the scanned invoice image for a supplier purchase.
     */
    public function uploadInvoiceImage(Request $request, SupplierPurchase $supplierPurchase)
    {
        $request->validate([
            'invoice_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ], [
            'invoice_image.required' => 'يرجى اختيار ملف الفاتورة.',
            'invoice_image.file'      => 'الملف غير صالح.',
            'invoice_image.mimes'     => 'الصيغ المقبولة: JPG, JPEG, PNG, WEBP, PDF.',
            'invoice_image.max'       => 'الحد الأقصى لحجم الملف 10 ميجابايت.',
        ]);

        // Remove old image
        if (
            $supplierPurchase->invoice_image_path &&
            \Illuminate\Support\Facades\Storage::disk('public')->exists($supplierPurchase->invoice_image_path)
        ) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($supplierPurchase->invoice_image_path);
        }

        $path = $request->file('invoice_image')->store('purchases/invoices', 'public');
        $supplierPurchase->update(['invoice_image_path' => $path]);

        return redirect()->route('supplier-purchases.show', $supplierPurchase)
            ->with('success', 'تم رفع صورة الفاتورة بنجاح');
    }
}
