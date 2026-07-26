<?php

namespace App\Http\Controllers;

use App\Models\LandRent;
use App\Models\LandRentPayment;
use App\Services\TreasuryService;
use Illuminate\Http\Request;

class LandRentController extends Controller
{
    public function __construct(private TreasuryService $treasuryService) {}

    public function index()
    {
        $landRents = LandRent::with(['payments'])->withSum('payments','amount')->orderBy('due_date')->paginate(20);
        return view('land-rent.index', compact('landRents'));
    }

    public function create() { return view('land-rent.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'month'        => 'required|integer|between:1,12',
            'year'         => 'required|integer',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'status'       => 'required|in:paid,pending',
            'notes'        => 'nullable|string',
        ]);

        $description = 'إيجار أرض - شهر ' . $request->month . ' / ' . $request->year;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $description) {
            $landRent = LandRent::create([
                'description'   => $description,
                'annual_amount' => $request->amount,
                'due_date'      => $request->payment_date,
                'notes'         => $request->notes,
            ]);

            if ($request->status === 'paid') {
                $payment = LandRentPayment::create([
                    'land_rent_id' => $landRent->id,
                    'payment_date' => $request->payment_date,
                    'amount'       => $request->amount,
                    'notes'        => $request->notes,
                ]);

                $this->treasuryService->recordOutgoing(
                    amount: (float)$request->amount,
                    category: 'land_rent',
                    description: 'إيجار أرض: ' . $description,
                    referenceType: 'land_rent_payment',
                    referenceId: $payment->id
                );
            }
        });

        return redirect()->route('land-rent.index')->with('success', 'تم تسجيل عقد الإيجار بنجاح');
    }

    public function show(LandRent $landRent)
    {
        $payments = $landRent->payments()->latest('payment_date')->get();
        return view('land-rent.show', compact('landRent','payments'));
    }

    public function edit(LandRent $landRent) { return view('land-rent.edit', compact('landRent')); }

    public function update(Request $request, LandRent $landRent)
    {
        $request->validate([
            'description'   => 'required|string|max:255',
            'annual_amount' => 'required|numeric|min:0',
            'due_date'      => 'required|date',
            'notes'         => 'nullable|string',
        ]);
        $landRent->update($request->validated());
        return redirect()->route('land-rent.show', $landRent)->with('success', 'تم تحديث العقد');
    }

    public function destroy(LandRent $landRent)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($landRent) {
            foreach ($landRent->payments as $payment) {
                $this->treasuryService->deleteTransaction('land_rent_payment', $payment->id);
                $payment->delete();
            }
            $landRent->delete();
        });
        return redirect()->route('land-rent.index')->with('success', 'تم حذف عقد الإيجار بجميع دفعاته من الخزينة بنجاح');
    }

    public function pay(Request $request, LandRent $landRent)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'notes'        => 'nullable|string',
        ]);

        $payment = LandRentPayment::create([
            'land_rent_id' => $landRent->id,
            'payment_date' => $request->payment_date,
            'amount'       => $request->amount,
            'notes'        => $request->notes,
        ]);

        $this->treasuryService->recordOutgoing(
            amount: (float)$request->amount,
            category: 'land_rent',
            description: 'إيجار أرض: ' . $landRent->description,
            referenceType: 'land_rent_payment',
            referenceId: $payment->id
        );

        return redirect()->route('land-rent.show', $landRent)->with('success', 'تم تسجيل الدفعة وخصمها من الخزينة');
    }
}
