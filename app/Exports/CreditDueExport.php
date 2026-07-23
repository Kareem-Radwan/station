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
            $query->when($this->request->customer_id, function ($q, $v) {
                $q->where('creditable_type', 'customer')->where('creditable_id', $v);
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

        $collection = $query->orderBy('due_date')->get()->map(fn($c) => [
            $c->creditable_type === 'customer' ? 'عميل' : 'مورد',
            $c->creditable?->name ?? '-',
            number_format($c->amount, 2),
            $c->due_date?->format('Y-m-d') ?? '-',
            $c->status_label,
        ]);

        $this->rowCount += $collection->count();

        return $collection;
    }

    public function headings(): array
    {
        return ['النوع', 'الطرف', 'المبلغ', 'تاريخ الاستحقاق', 'الحالة'];
    }

    public function styles(Worksheet $sheet): array
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows style
        if ($this->rowCount > 1) {
            $sheet->getStyle("A2:E{$this->rowCount}")->applyFromArray([
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
