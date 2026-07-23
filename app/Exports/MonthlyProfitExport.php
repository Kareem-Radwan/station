<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MonthlyProfitExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private int $month, private int $year) {}

    public function collection()
    {
        $start = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfMonth()->toDateString();
        $end   = \Carbon\Carbon::create($this->year, $this->month, 1)->endOfMonth()->toDateString();

        $treasuryIn = \App\Models\TreasuryTransaction::where('type', 'in')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalRevenue = (float) (clone $treasuryIn)->sum('amount');
        $customerPayments = (float) (clone $treasuryIn)->where('category', 'customer_payment')->sum('amount');
        $otherRevenue = $totalRevenue - $customerPayments;

        $treasuryOut = \App\Models\TreasuryTransaction::where('type', 'out')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalExpense = (float) (clone $treasuryOut)->sum('amount');
        $purchaseCost = (float) (clone $treasuryOut)->where('category', 'supplier_payment')->sum('amount');
        $payrollCost = (float) (clone $treasuryOut)->whereIn('category', ['salary', 'overtime'])->sum('amount');
        $rentalsCost = (float) (clone $treasuryOut)->whereIn('category', ['land_rent', 'rental', 'rental_maintenance'])->sum('amount');
        $generalExpenses = $totalExpense - $purchaseCost - $payrollCost - $rentalsCost;
        
        $netProfit = $totalRevenue - $totalExpense;

        $months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                   7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
        $period = ($months[$this->month] ?? $this->month) . ' ' . $this->year;

        return collect([
            ['الفترة', $period],
            ['', ''],
            ['الإيرادات', ''],
            ['- مبيعات (مقبوضات من العملاء)', number_format($customerPayments, 2)],
            ['- إيرادات أخرى', number_format($otherRevenue, 2)],
            ['إجمالي الإيرادات', number_format($totalRevenue, 2)],
            ['', ''],
            ['المصروفات', ''],
            ['- مشتريات المواد', number_format($purchaseCost, 2)],
            ['- الرواتب والأجور (المدفوعة)', number_format($payrollCost, 2)],
            ['- الإيجارات', number_format($rentalsCost, 2)],
            ['- المصروفات العامة', number_format($generalExpenses, 2)],
            ['إجمالي المصروفات', number_format($totalExpense, 2)],
            ['', ''],
            ['صافي الربح / الخسارة', number_format($netProfit, 2)],
        ]);
    }

    public function headings(): array { return ['البيان','القيمة']; }

    public function styles(Worksheet $sheet): array 
    { 
        $sheet->setRightToLeft(true);

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Table Main Headers
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // General structure layout cells
        $sheet->getStyle('A2:B16')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Bold category labels rows
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A9')->getFont()->setBold(true);

        // Highlight Section Totals
        $sheet->getStyle('A7:B7')->applyFromArray([ // Total Revenue
            'font' => ['bold' => true, 'color' => ['rgb' => '10B981']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']]
        ]);
        $sheet->getStyle('A14:B14')->applyFromArray([ // Total Expense
            'font' => ['bold' => true, 'color' => ['rgb' => 'EF4444']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']]
        ]);
        $sheet->getStyle('A16:B16')->applyFromArray([ // Net Profit/Loss
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'D97706']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']]
        ]);

        return []; 
    }

    public function title(): string
    {
        return 'الأرباح الخسائر الشهرية';
    }
}