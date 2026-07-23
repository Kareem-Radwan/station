<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Services\PayrollService;
use App\Services\TreasuryService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService,
        private TreasuryService $treasuryService
    ) {}

    public function index(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        $payrolls = Payroll::with('employee')
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->paginate(30);

        $employees = Employee::active()->orderBy('name')->get();

        return view('payroll.index', compact('payrolls','employees','month','year'));
    }

    public function calculateForm()
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('payroll.calculate', compact('employees'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'month'       => 'required|integer|min:1|max:12',
            'year'        => 'required|integer|min:2020|max:2100',
        ]);

        $employees = Employee::active()->get();

        if ($employees->isEmpty()) {
            return redirect()->route('payroll.index')->with('error', 'لا يوجد موظفون نشطون لاحتساب رواتبهم');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($employees, $request) {
            foreach ($employees as $employee) {
                $this->payrollService->calculateMonthlyPayroll(
                    $employee->id,
                    (int)$request->month,
                    (int)$request->year
                );
            }
        });

        return redirect()->route('payroll.index', [
            'month' => $request->month,
            'year'  => $request->year
        ])->with('success', 'تم احتساب رواتب جميع الموظفين بنجاح');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['employee', 'employee.borrows' => function ($query) {
            $query->active()->orderBy('borrow_date');
        }]);
        
        return view('payroll.show', compact('payroll'));
    }

    public function markPaid(Request $request, Payroll $payroll)
    {
        $request->validate([
            'borrow_deductions' => 'nullable|array',
            'borrow_deductions.*.borrow_id' => 'required|exists:employee_borrows,id',
            'borrow_deductions.*.amount' => 'required|numeric|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $payroll) {
            $totalBorrowDeductions = 0;

            // Process borrow deductions if provided
            if ($request->has('borrow_deductions')) {
                foreach ($request->borrow_deductions as $deduction) {
                    if ($deduction['amount'] > 0) {
                        $borrow = \App\Models\EmployeeBorrow::find($deduction['borrow_id']);
                        
                        // Validate amount doesn't exceed remaining
                        $amount = min((float)$deduction['amount'], (float)$borrow->remaining_amount);
                        
                        // Create borrow deduction record
                        \App\Models\EmployeeBorrowDeduction::create([
                            'borrow_id' => $borrow->id,
                            'payroll_id' => $payroll->id,
                            'amount' => $amount,
                            'deduction_date' => now()->toDateString(),
                        ]);

                        // Update borrow remaining amount
                        $newRemaining = (float)$borrow->remaining_amount - $amount;
                        $borrow->update([
                            'remaining_amount' => $newRemaining,
                            'status' => $newRemaining <= 0 ? 'paid' : 'active',
                        ]);

                        // Add to treasury (return borrowed money)
                        $this->treasuryService->recordIncoming(
                            amount: $amount,
                            category: 'employee_borrow_repayment',
                            description: "سداد سلفة من راتب: {$payroll->employee->name}",
                            transactionDate: null,
                            referenceType: \App\Models\EmployeeBorrowDeduction::class,
                            referenceId: $borrow->id
                        );

                        $totalBorrowDeductions += $amount;
                    }
                }
            }

            $newNetSalary = (float)$payroll->base_salary 
                + (float)$payroll->overtime_pay 
                - (float)$payroll->total_deductions 
                - $totalBorrowDeductions;

            $payroll->update([
                'borrow_deductions' => $totalBorrowDeductions,
                'net_salary' => max(0, $newNetSalary),
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ]);

            // Record net salary payment in treasury
            $this->treasuryService->recordOutgoing(
                amount: max(0, $newNetSalary),
                category: 'salary',
                description: 'راتب ' . $payroll->employee->name . ' - ' . $payroll->period_label,
                referenceType: Payroll::class,
                referenceId: $payroll->id
            );
        });

        return back()->with('success', 'تم تأكيد صرف الراتب وخصم السلف وتسجيلها في الخزينة');
    }
}
