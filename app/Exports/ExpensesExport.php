<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExpensesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithCustomCsvSettings
{
    private $rowCount = 1;

    public function __construct(
        private string $fromDate,
        private string $toDate,
        private ?string $category = null
    ) {}

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }

    public function collection()
    {
        $query = Expense::with(['recordedBy', 'contributor'])
            ->whereBetween('expense_date', [$this->fromDate, $this->toDate])
            ->when($this->category, fn($q, $v) => $q->where('category', $v))
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate total
        $totalAmount = $query->sum('amount');
        
        // Get category label
        $categoryLabel = 'الكل';
        if ($this->category && $query->isNotEmpty()) {
            $firstExpense = new Expense(['category' => $this->category]);
            $categoryLabel = $firstExpense->category_label;
        }

        // Add header rows with totals
        $data = collect([
            ['تقرير المصروفات', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['من تاريخ', $this->fromDate, '', '', '', '', '', ''],
            ['إلى تاريخ', $this->toDate, '', '', '', '', '', ''],
            ['الفئة', $categoryLabel, '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['إجمالي المصروفات', number_format($totalAmount, 2) . ' جنية', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            ['التاريخ', 'الفئة', 'الوصف', 'المبلغ', 'طريقة الدفع', 'اسم المساهم', 'ملاحظات', 'المسجل بواسطة'],
        ]);

        $this->rowCount = $data->count();

        // Add data rows
        foreach ($query as $expense) {
            $this->rowCount++;
            
            $paymentMethod = 'نقدي من الخزينة';
            $contributorName = '-';
            
            if ($expense->reference_type === 'contributor' && $expense->contributor) {
                $paymentMethod = 'مساهم';
                $contributorName = $expense->contributor->name;
            }
            
            $data->push([
                \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d'),
                $expense->category_label,
                $expense->description,
                number_format($expense->amount, 2),
                $paymentMethod,
                $contributorName,
                $expense->notes ?? '-',
                $expense->recordedBy?->name ?? '-',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Title styling (row 1)
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info rows styling (rows 3-5)
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        
        // Total row styling (row 7)
        $sheet->getStyle('A7:B7')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FF0000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB3B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Table header row (row 10)
        $sheet->getStyle('A10:H10')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows styling (starting from row 11)
        if ($this->rowCount > 10) {
            $sheet->getStyle("A11:H{$this->rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'تقرير المصروفات';
    }
}
