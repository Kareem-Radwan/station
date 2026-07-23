<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index(Request $request)
    {
        $employees      = Employee::active()->orderBy('name')->get();
        $fromDate       = $request->from_date ?? today()->startOfMonth()->toDateString();
        $toDate         = $request->to_date   ?? today()->toDateString();
        $filterEmployee = $request->employee_id;

        // Fetch all matching attendance records (no pagination — grouped by employee)
        $query = Attendance::with('employee')
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'asc');

        if ($filterEmployee) {
            $query->where('employee_id', $filterEmployee);
        }

        $allRecords = $query->get();

        // Fetch deductions for the same period keyed by [employee_id][date]
        $deductionQuery = EmployeeDeduction::whereBetween('deduction_date', [$fromDate, $toDate]);
        if ($filterEmployee) {
            $deductionQuery->where('employee_id', $filterEmployee);
        }
        $deductionsMap = $deductionQuery->get()
            ->groupBy('employee_id')
            ->map(fn($rows) => $rows->keyBy(fn($r) => $r->deduction_date->toDateString()));

        // Group attendance records by employee
        $grouped = $allRecords->groupBy('employee_id')->map(function ($records) use ($deductionsMap) {
            $employee = $records->first()->employee;
            $days     = $records->map(function ($att) use ($deductionsMap) {
                $deduction = $deductionsMap[$att->employee_id][$att->date->toDateString()]->amount ?? 0;
                return (object)[
                    'id'             => $att->id,
                    'date'           => $att->date,
                    'time_in'        => $att->time_in,
                    'time_out'       => $att->time_out,
                    'hours_worked'   => $att->hours_worked,
                    'overtime_hours' => (float)$att->overtime_hours,
                    'status'         => $att->status,
                    'status_label'   => $att->status_label,
                    'notes'          => $att->notes,
                    'deduction'      => (float)$deduction,
                    'attendance'     => $att,
                ];
            });

            return (object)[
                'employee'       => $employee,
                'days'           => $days,
                'days_present'   => $days->whereIn('status', ['present', 'half_day'])->count(),
                'days_absent'    => $days->where('status', 'absent')->count(),
                'total_hours'    => $days->sum('hours_worked'),
                'total_overtime' => $days->sum('overtime_hours'),
                'total_deductions' => $days->sum('deduction'),
            ];
        })->values();

        return view('attendance.index', compact('employees', 'grouped', 'fromDate', 'toDate', 'filterEmployee'));
    }

    public function create()
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'time_in'     => 'nullable|date_format:H:i',
            'time_out'    => 'nullable|date_format:H:i',
            'deduction'   => 'nullable|numeric|min:0',
            'notes'       => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $status = $request->filled('time_in') ? 'present' : 'absent';
            $att = $this->payrollService->recordAttendance(
                $request->employee_id,
                $request->date,
                $request->time_in,
                $request->time_out,
                $status
            );

            $att->update(['notes' => $request->notes]);

            if ($request->deduction > 0) {
                \App\Models\EmployeeDeduction::updateOrCreate(
                    [
                        'employee_id'    => $request->employee_id,
                        'deduction_date' => $request->date,
                    ],
                    [
                        'type'        => 'other',
                        'amount'      => $request->deduction,
                        'reason'      => 'خصم يوم الحضور ' . $request->date,
                        'recorded_by' => auth()->id(),
                    ]
                );
            } else {
                \App\Models\EmployeeDeduction::where('employee_id', $request->employee_id)
                    ->where('deduction_date', $request->date)
                    ->delete();
            }
        });

        return redirect()->route('attendance.index', ['date' => $request->date])->with('success', 'تم تسجيل الحضور');
    }

    public function edit(Attendance $attendance)
    {
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'time_in'   => 'nullable|date_format:H:i',
            'time_out'  => 'nullable|date_format:H:i',
            'deduction' => 'nullable|numeric|min:0',
            'notes'     => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $attendance) {
            $status = $request->filled('time_in') ? 'present' : 'absent';
            $this->payrollService->recordAttendance(
                $attendance->employee_id,
                $attendance->date->toDateString(),
                $request->time_in,
                $request->time_out,
                $status
            );

            $attendance->update(['notes' => $request->notes]);

            if ($request->deduction > 0) {
                \App\Models\EmployeeDeduction::updateOrCreate(
                    [
                        'employee_id'    => $attendance->employee_id,
                        'deduction_date' => $attendance->date->toDateString(),
                    ],
                    [
                        'type'        => 'other',
                        'amount'      => $request->deduction,
                        'reason'      => 'خصم يوم الحضور ' . $attendance->date->toDateString(),
                        'recorded_by' => auth()->id(),
                    ]
                );
            } else {
                \App\Models\EmployeeDeduction::where('employee_id', $attendance->employee_id)
                    ->where('deduction_date', $attendance->date->toDateString())
                    ->delete();
            }
        });

        return redirect()->route('attendance.index')->with('success', 'تم تحديث سجل الحضور');
    }
}
