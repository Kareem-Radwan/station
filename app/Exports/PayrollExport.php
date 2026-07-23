<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PayrollExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowNumber = 1;

    public function __construct(
        private ?int $month = null, 
        private ?int $year = null,
        private ?int $employeeId = null
    ) {}

    public function collection()
    {
        $query = \App\Models\Payroll::with(['employee', 'borrowDeductions.borrow', 'employee.borrows'])
            ->when($this->month, fn($q, $v) => $q->where('period_month', $v))
            ->when($this->year, fn($q, $v) => $q->where('period_year', $v))
            ->when($this->employeeId, fn($q, $v) => $q->where('employee_id', $v))
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();

        return $query->map(function($p) {
            $this->rowNumber++;
            
            // Calculate borrow deducted for this payroll
            $borrowDeducted = $p->borrowDeductions->sum('amount');
            
            // Calculate remaining borrow balance for this employee
            $activeBorrows = $p->employee->borrows()->where('status', 'active')->get();
            $totalRemainingBorrow = $activeBorrows->sum('remaining_amount');
            
            return [
                $p->employee->name,
                $p->period_month . '/' . $p->period_year,
                number_format($p->base_salary, 2),
                number_format($p->overtime_pay, 2),
                number_format($p->total_deductions, 2),
                number_format($borrowDeducted, 2),
                number_format($totalRemainingBorrow, 2),
                number_format($p->net_salary, 2),
                $p->status_label,
            ];
        });
    }

    public function headings(): array { 
        return ['الموظف','الشهر/السنة','الراتب الأساسي','الإضافي','الخصومات','خصم سلفة','رصيد السلفة المتبقي','الصافي','الحالة']; 
    }
    
    public function styles(Worksheet $sheet): array { 
        $sheet->setRightToLeft(true);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Dynamic rows parsing
        $rowCount = $this->rowNumber;
        if ($rowCount > 1) {
            $sheet->getStyle("A2:I{$rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return []; 
    }

    public function title(): string
    {
        return 'مسيرات الرواتب';
    }
}