<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\EmployeeDeduction;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollService
{
    const NORMAL_HOURS = 10; // 08:00–18:00

    public function recordAttendance(int $employeeId, string $date, ?string $checkIn, ?string $checkOut, string $status = 'present'): Attendance
    {
        $normalHours = 0;
        $overtimeHours = 0;

        if ($checkIn && $checkOut && $status === 'present') {
            $in  = Carbon::createFromTimeString($checkIn);
            $out = Carbon::createFromTimeString($checkOut);
            $totalHours   = $in->diffInMinutes($out) / 60;
            $normalHours  = min($totalHours, self::NORMAL_HOURS);
            $overtimeHours = max(0, $totalHours - self::NORMAL_HOURS);
        }

        return Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date],
            [
                'check_in'       => $checkIn,
                'check_out'      => $checkOut,
                'normal_hours'   => round($normalHours, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'status'         => $status,
            ]
        );
    }

    public function calculateMonthlyPayroll(int $employeeId, int $month, int $year): Payroll
    {
        $employee  = Employee::findOrFail($employeeId);
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalOvertimeHours = $attendances->sum('overtime_hours');
        $overtimeRate       = $employee->getMonthlyOvertimeRate();
        $overtimePay        = round($totalOvertimeHours * $overtimeRate, 2);

        $totalDeductions = EmployeeDeduction::where('employee_id', $employeeId)
            ->whereBetween('deduction_date', [$startDate, $endDate])
            ->sum('amount');

        $netSalary = (float)$employee->base_salary + $overtimePay - (float)$totalDeductions;

        return Payroll::updateOrCreate(
            ['employee_id' => $employeeId, 'period_month' => $month, 'period_year' => $year],
            [
                'base_salary'      => $employee->base_salary,
                'overtime_pay'     => $overtimePay,
                'total_deductions' => $totalDeductions,
                'net_salary'       => max(0, $netSalary),
                'status'           => 'pending',
            ]
        );
    }
}
