<?php

namespace App\Accounting\Exports;

use App\Accounting\Reports\TrialBalanceReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class TrialBalanceExportAccounting implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    private Collection $rows;
    private array $totals;

    public function __construct(
        private readonly TrialBalanceReport $report,
        private readonly ?string $fromDate = null,
        private readonly ?string $toDate   = null,
    ) {
        $this->rows   = $report->generate($fromDate, $toDate);
        $this->totals = $report->totals($fromDate, $toDate);
    }

    public function title(): string
    {
        return 'ميزان المراجعة';
    }

    public function headings(): array
    {
        return [
            ['ميزان المراجعة — نظام القيد المزدوج'],
            ['الفترة: ' . ($this->fromDate ?? 'البداية') . ' → ' . ($this->toDate ?? now()->toDateString())],
            [],
            ['رقم الحساب', 'اسم الحساب', 'نوع الحساب', 'مدين', 'دائن', 'صافي الرصيد', 'طبيعة الحساب'],
        ];
    }

    public function collection(): Collection
    {
        $typeLabels = [
            'asset'     => 'أصول',
            'liability' => 'خصوم',
            'equity'    => 'حقوق الملكية',
            'revenue'   => 'إيرادات',
            'expense'   => 'مصروفات',
        ];

        $data = $this->rows->map(fn($row) => [
            $row->account_number,
            $row->account_name,
            $typeLabels[$row->account_type] ?? $row->account_type,
            $row->total_debit  > 0 ? $row->total_debit  : '',
            $row->total_credit > 0 ? $row->total_credit : '',
            abs($row->net_balance),
            $row->normal_balance === 'debit' ? 'مدين' : 'دائن',
        ]);

        // Totals row
        $data->push([]);
        $data->push([
            'الإجمالي',
            '',
            '',
            $this->totals['total_debit'],
            $this->totals['total_credit'],
            '',
            $this->totals['balanced'] ? 'متوازن ✓' : 'غير متوازن ✗',
        ]);

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 40,
            'C' => 18,
            'D' => 18,
            'E' => 18,
            'F' => 18,
            'G' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font'      => ['italic' => true, 'color' => ['argb' => 'FF6B7280']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge title cells
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');

                // RTL direction
                $sheet->setRightToLeft(true);

                // Auto-filter on header row
                $sheet->setAutoFilter('A4:G4');

                // Freeze top 4 rows
                $sheet->freezePane('A5');

                // Style the totals row (last row)
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A{$lastRow}:G{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF59E0B']],
                ]);
            },
        ];
    }
}
