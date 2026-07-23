<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(private \App\Services\TreasuryService $treasuryService) {}

    public function index(Request $request)
    {
        $receipts = Receipt::query()
            ->when($request->type, fn($q,$v) => $q->where('type',$v))
            ->latest('receipt_date')
            ->paginate(20)->withQueryString();
        return view('receipts.index', compact('receipts'));
    }

    public function create()
    {
        return view('receipts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'           => 'required|in:in,out',
            'receipt_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'recipient_name' => 'required|string|max:255',
            'description'    => 'required|string',
        ]);

        $receipt = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $receipt = Receipt::create([
                'type'           => $request->type,
                'receipt_date'   => $request->receipt_date,
                'amount'         => $request->amount,
                'total_amount'   => $request->amount, // fallback for schema constraint
                'recipient_name' => $request->recipient_name,
                'description'    => $request->description,
                'recorded_by'    => auth()->id(),
            ]);

            // Add treasury record
            if ($request->type === 'in') {
                $this->treasuryService->recordIncoming(
                    amount: (float)$request->amount,
                    category: 'receipt_in',
                    description: 'سند قبض #' . $receipt->id . ' - ' . $request->recipient_name . ' - ' . $request->description,
                    referenceType: 'receipt',
                    referenceId: $receipt->id
                );
            } else {
                $this->treasuryService->recordOutgoing(
                    amount: (float)$request->amount,
                    category: 'receipt_out',
                    description: 'سند صرف #' . $receipt->id . ' - ' . $request->recipient_name . ' - ' . $request->description,
                    referenceType: 'receipt',
                    referenceId: $receipt->id
                );
            }

            return $receipt;
        });

        return redirect()->route('receipts.index')->with('success', 'تم حفظ السند بنجاح');
    }

    public function show(Receipt $receipt)
    {
        return view('receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt)
    {
        return view('receipts.edit', compact('receipt'));
    }

    public function update(Request $request, Receipt $receipt)
    {
        $request->validate([
            'type'           => 'required|in:in,out',
            'receipt_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'recipient_name' => 'required|string|max:255',
            'description'    => 'required|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $receipt) {
            $receipt->update([
                'type'           => $request->type,
                'receipt_date'   => $request->receipt_date,
                'amount'         => $request->amount,
                'total_amount'   => $request->amount,
                'recipient_name' => $request->recipient_name,
                'description'    => $request->description,
            ]);

            // Re-sync treasury record
            $this->treasuryService->deleteTransaction('receipt', $receipt->id);
            if ($request->type === 'in') {
                $this->treasuryService->recordIncoming(
                    amount: (float)$request->amount,
                    category: 'receipt_in',
                    description: 'سند قبض #' . $receipt->id . ' - ' . $request->recipient_name . ' - ' . $request->description,
                    referenceType: 'receipt',
                    referenceId: $receipt->id
                );
            } else {
                $this->treasuryService->recordOutgoing(
                    amount: (float)$request->amount,
                    category: 'receipt_out',
                    description: 'سند صرف #' . $receipt->id . ' - ' . $request->recipient_name . ' - ' . $request->description,
                    referenceType: 'receipt',
                    referenceId: $receipt->id
                );
            }
        });

        return redirect()->route('receipts.show', $receipt)->with('success', 'تم تحديث السند');
    }

    public function destroy(Receipt $receipt)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($receipt) {
            $this->treasuryService->deleteTransaction('receipt', $receipt->id);
            // Delete signed image file if it exists
            if ($receipt->signed_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($receipt->signed_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($receipt->signed_image_path);
            }
            $receipt->delete();
        });
        return redirect()->route('receipts.index')->with('success', 'تم حذف السند');
    }

    /**
     * Toggle the receipt status between pending and done.
     */
    public function markDone(Receipt $receipt)
    {
        $newStatus = $receipt->status === 'pending' ? 'done' : 'pending';
        $receipt->update(['status' => $newStatus]);

        $label = $newStatus === 'done' ? 'تم تحديد السند كمنتهٍ' : 'تم إعادة السند إلى حالة معلق';
        return redirect()->route('receipts.show', $receipt)->with('success', $label);
    }

    /**
     * Upload the signed/scanned image for a completed receipt.
     */
    public function uploadSignedImage(Request $request, Receipt $receipt)
    {
        $request->validate([
            'signed_image' => 'required|image|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ], [
            'signed_image.required' => 'يرجى اختيار صورة السند الموقعة.',
            'signed_image.image'    => 'يجب أن يكون الملف صورة.',
            'signed_image.mimes'    => 'الصيغ المقبولة: JPG, PNG, WEBP, PDF.',
            'signed_image.max'      => 'الحد الأقصى لحجم الملف 5 ميجابايت.',
        ]);

        // Delete old image if exists
        if ($receipt->signed_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($receipt->signed_image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($receipt->signed_image_path);
        }

        $path = $request->file('signed_image')->store('receipts/signed', 'public');
        $receipt->update(['signed_image_path' => $path]);

        return redirect()->route('receipts.show', $receipt)->with('success', 'تم رفع صورة السند الموقعة بنجاح');
    }
}
