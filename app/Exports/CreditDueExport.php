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

class CreditDueExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $request;
    private $rowCount = 1;

    public function __construct($request = null)
    {
        $this->request = $request;
    }

    public function collection()
    {
        \App\Models\Credit::checkAndMarkOverdue();

        $query = \App\Models\Credit::with('creditable');

        if ($this->request) {
            $query->when($this->request->creditable_type, function ($q, $v) {
                if ($v === 'customer') {
                    $q->where('creditable_type', 'customer');
                } elseif ($v === 'supplier') {
                    $q->where('creditable_type', 'supplier');
                }
            })
            ->when($this->request->customer_id, function ($q, $v) {
                $q->where('creditable_type', 'customer')->where('creditable_id', $v);
            })
            ->when($this->request->supplier_id, function ($q, $v) {
                $q->where('creditable_type', 'supplier')->where('creditable_id', $v);
            })
            ->when($this->request->status, function ($q, $v) {
                if ($v === 'active') {
                    $q->where('status', 'pending');
                } else {
                    $q->where('status', $v);
                }
            });
        } else {
            $query->where('status', '!=', 'paid');
        }

        $credits = $query->orderBy('due_date')->get();
        
        // Calculate totals
        $totalAmount = $credits->sum('amount');
        $totalPaid = $credits->where('status', 'paid')->sum('amount');
        $totalRemaining = $totalAmount - $totalPaid;
        
        // Count by type
        $customerCredits = $credits->where('creditable_type', 'customer');
        $supplierCredits = $credits->where('creditable_type', 'supplier');
        $customerCount = $customerCredits->count();
        $supplierCount = $supplierCredits->count();
        $customerTotal = $customerCredits->sum('amount');
        $supplierTotal = $supplierCredits->sum('amount');
        
        // Count by status
        $pendingCount = $credits->where('status', 'pending')->count();
        $overdueCount = $credits->where('status', 'overdue')->count();
        $paidCount = $credits->where('status', 'paid')->count();

        // Determine filter labels
        $creditableTypeLabel = 'الكل (عملاء وموردين)';
        if ($this->request && $this->request->creditable_type === 'customer') {
            $creditableTypeLabel = 'عملاء فقط';
        } elseif ($this->request && $this->request->creditable_type === 'supplier') {
            $creditableTypeLabel = 'موردين فقط';
        }
        
        $statusLabel = 'الكل';
        if ($this->request && $this->request->status) {
            $statusLabel = match($this->request->status) {
                'active' => 'نشط (معلق)',
                'overdue' => 'متأخر (تجاوز الاستحقاق)',
                'paid' => 'مسدد',
                default => 'الكل'
            };
        }

        // Build header rows with summary
        $data = collect([
            ['تقرير الديون والآجل', '', '', '', ''],
            ['', '', '', '', ''],
            ['نوع الفلتر', $creditableTypeLabel, '', '', ''],
            ['حالة الدين', $statusLabel, '', '', ''],
            ['', '', '', '', ''],
            ['ملخص الديون', '', '', '', ''],
            ['إجمالي المبلغ', number_format($totalAmount, 2) . ' جنية', '', '', ''],
            ['إجمالي المسدد', number_format($totalPaid, 2) . ' جنية', '', '', ''],
            ['المتبقي', number_format($totalRemaining, 2) . ' جنية', '', '', ''],
            ['', '', '', '', ''],
            ['التوزيع حسب النوع', '', '', '', ''],
            ['ديون العملاء', number_format($customerTotal, 2) . ' جنية', 'عدد: ' . $customerCount, '', ''],
            ['ديون الموردين', number_format($supplierTotal, 2) . ' جنية', 'عدد: ' . $supplierCount, '', ''],
            ['', '', '', '', ''],
            ['التوزيع حسب الحالة', '', '', '', ''],
            ['نشط (معلق)', $pendingCount, '', '', ''],
            ['متأخر', $overdueCount, '', '', ''],
            ['مسدد', $paidCount, '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['النوع', 'الطرف', 'المبلغ', 'المسدد', 'المتبقي', 'تاريخ الاستحقاق', 'الحالة'],
        ]);

        $this->rowCount = $data->count();

        // Add data rows
        foreach ($credits as $c) {
            $this->rowCount++;
            $data->push([
                $c->creditable_type === 'customer' ? 'عميل' : 'مورد',
                $c->creditable?->name ?? '-',
                number_format($c->amount, 2),
                number_format($c->paid_amount, 2),
                number_format($c->remaining_amount, 2),
                $c->due_date?->format('Y-m-d') ?? '-',
                $c->status_label,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet): array
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Title styling (row 1)
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Filter info styling (rows 3-4)
        $sheet->getStyle('A3:A4')->getFont()->setBold(true);

        // Summary section title (row 6)
        $sheet->mergeCells('A6:G6');
        $sheet->getStyle('A6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
        ]);

        // Summary rows styling (rows 7-9)
        $sheet->getStyle('A7:A9')->getFont()->setBold(true);
        $sheet->getStyle('A7:B9')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF9C4']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // Highlight remaining amount (row 9)
        $sheet->getStyle('A9:B9')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFCDD2']],
        ]);

        // Distribution by type section (rows 11-13)
        $sheet->mergeCells('A11:G11');
        $sheet->getStyle('A11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
        ]);
        $sheet->getStyle('A12:C13')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A12:A13')->getFont()->setBold(true);

        // Distribution by status section (rows 15-18)
        $sheet->mergeCells('A15:G15');
        $sheet->getStyle('A15')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3E0']],
        ]);
        $sheet->getStyle('A16:B18')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A16:A18')->getFont()->setBold(true);

        // Table header row (row 21)
        $sheet->getStyle('A21:G21')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows styling (starting from row 22)
        if ($this->rowCount > 21) {
            $sheet->getStyle("A22:G{$this->rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'الديون والمستحقات';
    }
}
