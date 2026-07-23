<?php

namespace App\Exports;

use App\Models\TreasuryTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TreasuryExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowNumber = 1;

    public function __construct(
        private string $fromDate, 
        private string $toDate,
        private ?string $type = null
    ) {}

    public function collection()
    {
        $query = TreasuryTransaction::query()
            ->whereBetween('transaction_date', [$this->fromDate, $this->toDate])
            ->when($this->type, fn($q, $v) => $q->where('type', $v))
            ->orderBy('id');

        $transactions = $query->get();

        $totalIn = (float) $transactions->where('type', 'in')->sum('amount');
        $totalOut = (float) $transactions->where('type', 'out')->sum('amount');
        $periodBalance = $totalIn - $totalOut;

        $data = collect([
            ['تقرير حركة الخزينة', ''],
            ['', ''],
            ['من تاريخ', $this->fromDate],
            ['إلى تاريخ', $this->toDate],
            ['', ''],
            ['إجمالي الوارد (خلال الفترة)', number_format($totalIn, 2)],
            ['إجمالي الصادر (خلال الفترة)', number_format($totalOut, 2)],
            ['الرصيد للفترة المحددة', number_format($periodBalance, 2)],
            ['', ''],
            ['', ''],
            ['التاريخ', 'النوع', 'الفئة', 'البيان', 'وارد', 'صادر', 'الرصيد التراكمي'],
        ]);

        $this->rowNumber = $data->count();

        foreach ($transactions as $t) {
            $this->rowNumber++;
            $data->push([
                $t->transaction_date->format('Y-m-d'),
                $t->type === 'in' ? 'وارد' : 'صادر',
                $t->category_label,
                $t->description ?? '-',
                $t->type === 'in' ? number_format($t->amount, 2) : '-',
                $t->type === 'out' ? number_format($t->amount, 2) : '-',
                number_format($t->balance_after, 2),
            ]);
        }

        return $data;
    }

    public function headings(): array { return []; }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Title row formatting
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        
        // Form properties meta titles fields bolding
        $sheet->getStyle('A3:A4')->getFont()->setBold(true);
        $sheet->getStyle('A6:A8')->getFont()->setBold(true);

        // Grid primary headers block styling (Row 11)
        $sheet->getStyle('A11:G11')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Dynamic table cells framing configurations
        if ($this->rowNumber > 11) {
            $sheet->getStyle("A12:G{$this->rowNumber}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'تقرير حركة الخزينة';
    }
}