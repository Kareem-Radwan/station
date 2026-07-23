<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeBorrow;
use App\Services\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeBorrowController extends Controller
{
    protected $treasuryService;

    public function __construct(TreasuryService $treasuryService)
    {
        $this->treasuryService = $treasuryService;
    }

    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'borrow_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create borrow record
            $borrow = EmployeeBorrow::create([
                'employee_id' => $employee->id,
                'amount' => $request->amount,
                'remaining_amount' => $request->amount,
                'borrow_date' => $request->borrow_date,
                'reason' => $request->reason,
                'status' => 'active',
                'recorded_by' => auth()->id(),
            ]);

            // Deduct from treasury
            $this->treasuryService->recordOutgoing(
                amount: $request->amount,
                category: 'employee_borrow',
                description: "سلفة للموظف: {$employee->name}",
                transactionDate: $request->borrow_date,
                referenceType: EmployeeBorrow::class,
                referenceId: $borrow->id
            );

            DB::commit();

            return redirect()
                ->route('employees.show', $employee)
                ->with('success', 'تم إضافة السلفة وخصمها من الخزينة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إضافة السلفة: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Employee $employee, EmployeeBorrow $borrow)
    {
        try {
            DB::beginTransaction();

            // Check if any deductions have been made
            if ($borrow->deductions()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف سلفة تم خصم منها في الرواتب');
            }

            // Return money to treasury
            $this->treasuryService->recordIncoming(
                amount: $borrow->amount,
                category: 'employee_borrow_return',
                description: "إلغاء سلفة للموظف: {$employee->name}",
                transactionDate: now()->toDateString(),
                referenceType: EmployeeBorrow::class,
                referenceId: $borrow->id
            );

            // Delete borrow
            $borrow->delete();

            DB::commit();

            return redirect()
                ->route('employees.show', $employee)
                ->with('success', 'تم حذف السلفة وإرجاعها للخزينة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف السلفة: ' . $e->getMessage());
        }
    }
}
