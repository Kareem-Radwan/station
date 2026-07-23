<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\EmployeeDeduction;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::withCount(['attendance','deductions'])->latest()->paginate(20);
        return view('employees.index', compact('employees'));
    }

    public function create() { return view('employees.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'position'      => 'nullable|string|max:100',
            'hire_date'     => 'nullable|date',
            'base_salary'   => 'required|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        Employee::create($request->all());

        return redirect()->route('employees.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function show(Employee $employee)
    {
        $recentAttendance = $employee->attendance()->latest('date')->take(30)->get();
        $recentDeductions = $employee->deductions()->latest('deduction_date')->take(10)->get();
        $recentPayroll    = $employee->payrolls()->latest('period_year','period_month')->take(6)->get();
        $borrows = $employee->borrows()->with('deductions')->latest('borrow_date')->get();
        
        return view('employees.show', compact('employee','recentAttendance','recentDeductions','recentPayroll','borrows'));
    }

    public function edit(Employee $employee) { return view('employees.edit', compact('employee')); }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'position'      => 'nullable|string|max:100',
            'base_salary'   => 'required|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'is_active'     => 'boolean',
            'notes'         => 'nullable|string',
        ]);

        $employee->update($request->validated());

        return redirect()->route('employees.show', $employee)->with('success', 'تم تحديث بيانات الموظف');
    }

    public function destroy(Employee $employee)
    {
        $employee->update(['is_active' => false]);
        return redirect()->route('employees.index')->with('success', 'تم إيقاف الموظف');
    }
}
