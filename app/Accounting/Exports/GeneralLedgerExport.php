<?php

namespace App\Accounting\Exports;

use App\Accounting\Reports\GeneralLedgerReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use App\Accounting\Helpers\ReferenceTypeMapper;

class GeneralLedgerExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    private array $ledger;

    public function __construct(
        private readonly GeneralLedgerReport $report,
        private readonly int     $accountId,
        private readonly ?string $fromDate = null,
        private readonly ?string $toDate   = null,
    ) {
        $this->ledger = $report->forAccount($accountId, $fromDate, $toDate);
    }

    public function title(): string
    {
        return 'دفتر الأستاذ';
    }

    public function headings(): array
    {
        $account = $this->ledger['account'] ?? null;
        return [
            ['دفتر الأستاذ العام — نظام القيد المزدوج'],
            ['الحساب: ' . ($account->account_number ?? '') . ' - ' . ($account->account_name ?? '')],
            ['الفترة: ' . ($this->fromDate ?? 'البداية') . ' → ' . ($this->toDate ?? now()->toDateString())],
            [],
            ['التاريخ', 'رقم القيد', 'البيان', 'المرجع', 'مدين', 'دائن', 'الرصيد الجاري'],
        ];
    }

    public function collection(): Collection
    {
        $lines = collect($this->ledger['lines'] ?? []);

        // Opening balance
        $data = collect([[
            $this->fromDate ?? '—',
            '—',
            'رصيد أول المدة',
            '—',
            '',
            '',
            number_format($this->ledger['opening_balance'] ?? 0, 2),
        ]]);

        // Lines
        $lines->each(function ($line) use (&$data) {
            $data->push([
                $line->date,
                $line->entry_no,
                $line->line_description ?: $line->entry_description,
                ReferenceTypeMapper::format($line->reference_type, $line->reference_id),
                $line->debit  > 0 ? number_format($line->debit,  2) : '',
                $line->credit > 0 ? number_format($line->credit, 2) : '',
                number_format($line->running_balance, 2),
            ]);
        });

        // Totals
        $data->push([]);
        $data->push([
            'الإجمالي',
            '',
            '',
            '',
            number_format($this->ledger['total_debit']  ?? 0, 2),
            number_format($this->ledger['total_credit'] ?? 0, 2),
            number_format($this->ledger['closing_balance'] ?? 0, 2),
        ]);

        return $data;
    }

    public function columnWidths(): array
    {
        return ['A' => 14, 'B' => 18, 'C' => 40, 'D' => 20, 'E' => 16, 'F' => 16, 'G' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');
                $sheet->setRightToLeft(true);
                $sheet->setAutoFilter('A5:G5');
                $sheet->freezePane('A6');

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A{$lastRow}:G{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF59E0B']],
                ]);
            },
        ];
    }
}
