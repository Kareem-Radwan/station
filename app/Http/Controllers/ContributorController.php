<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\ContributorPayment;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContributorController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Contributor::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $contributors = $query->orderBy('name')->paginate(20)->withQueryString();

        // Summary stats
        $totalPaid         = (float) ContributorPayment::whereNull('treasury_transaction_id')->sum('amount');
        $totalShareAmount  = (float) Contributor::sum('share_amount') + $totalPaid;
        $totalOutstanding  = max(0, $totalShareAmount - $totalPaid);
        $totalSharePercent = Contributor::where('is_active', true)->sum('share_percentage');

        return view('contributors.index', compact(
            'contributors',
            'totalShareAmount',
            'totalPaid',
            'totalOutstanding',
            'totalSharePercent'
        ));
    }

    // ─── Create / Store ────────────────────────────────────────────────────────

    public function create()
    {
        return view('contributors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'share_percentage' => 'required|numeric|min:0|max:100',
            'share_amount'     => 'required|numeric|min:0',
            'national_id'      => 'nullable|string|max:50',
            'address'          => 'nullable|string|max:500',
            'notes'            => 'nullable|string',
            'is_active'        => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Contributor::create($validated);

        return redirect()->route('contributors.index')
            ->with('success', 'تم إضافة المساهم بنجاح');
    }

    // ─── Show ──────────────────────────────────────────────────────────────────

    public function show(Contributor $contributor)
    {
        $payments = $contributor->payments()
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('contributors.show', compact('contributor', 'payments'));
    }

    // ─── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(Contributor $contributor)
    {
        return view('contributors.edit', compact('contributor'));
    }

    public function update(Request $request, Contributor $contributor)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'share_percentage' => 'required|numeric|min:0|max:100',
            'share_amount'     => 'required|numeric|min:0',
            'national_id'      => 'nullable|string|max:50',
            'address'          => 'nullable|string|max:500',
            'notes'            => 'nullable|string',
            'is_active'        => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $contributor->update($validated);

        return redirect()->route('contributors.show', $contributor)
            ->with('success', 'تم تحديث بيانات المساهم بنجاح');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Contributor $contributor)
    {
        if ($contributor->payments()->exists()) {
            return back()->with('error', 'لا يمكن حذف المساهم لوجود مدفوعات مسجلة');
        }

        $contributor->delete();

        return redirect()->route('contributors.index')
            ->with('success', 'تم حذف المساهم');
    }

    // ─── Add To Share Amount ───────────────────────────────────────────────────

    /**
     * Add money to contributor's share amount and record in treasury.
     */
    public function addToShare(Request $request, Contributor $contributor)
    {
        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $contributor) {
            // 1. Increase contributor's share_amount
            $contributor->increment('share_amount', $validated['amount']);

            // 2. Create treasury IN transaction
            $treasury = TreasuryTransaction::create([
                'type'             => 'in',
                'amount'           => $validated['amount'],
                'category'         => 'contributor_payment',
                'description'      => 'زيادة رأس مال مساهم: ' . $contributor->name,
                'transaction_date' => $validated['payment_date'],
                'balance_after'    => $this->calculateBalanceAfter($validated['amount'], 'in'),
                'recorded_by'      => auth()->id(),
            ]);

            // 3. Create contributor payment record
            ContributorPayment::create([
                'contributor_id'           => $contributor->id,
                'amount'                   => $validated['amount'],
                'payment_date'             => $validated['payment_date'],
                'payment_method'           => 'cash',
                'notes'                    => $validated['notes'] ?? 'زيادة رأس المال',
                'treasury_transaction_id'  => $treasury->id,
            ]);
        });

        return back()->with('success', 'تم إضافة المبلغ إلى رأس المال والخزينة بنجاح');
    }

    // ─── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Calculate the running balance after this transaction.
     */
    private function calculateBalanceAfter(float $amount, string $type): float
    {
        $lastBalance = (float) TreasuryTransaction::orderBy('id', 'desc')
            ->value('balance_after') ?? 0;

        return $type === 'in'
            ? $lastBalance + $amount
            : $lastBalance - $amount;
    }
}
