<?php

namespace App\Accounting\Exports;

use App\Accounting\Models\JournalEntry;
use App\Accounting\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
use App\Accounting\Helpers\ReferenceTypeMapper;

class JournalBookExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    public function __construct(
        private readonly ?string $fromDate = null,
        private readonly ?string $toDate   = null,
    ) {}

    public function title(): string
    {
        return 'دفتر اليومية';
    }

    public function headings(): array
    {
        return [
            ['دفتر اليومية العامة — نظام القيد المزدوج'],
            ['الفترة: ' . ($this->fromDate ?? 'البداية') . ' → ' . ($this->toDate ?? now()->toDateString())],
            [],
            ['التاريخ', 'رقم القيد', 'البيان', 'المرجع', 'رقم الحساب', 'اسم الحساب', 'مدين', 'دائن'],
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('journal_entries as je')
            ->join('journal_entry_lines as jel', 'jel.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->where('je.status', 'posted')
            ->select([
                'je.date',
                'je.entry_no',
                'je.description as entry_desc',
                'je.reference_type',
                'je.reference_id',
                'a.account_number',
                'a.account_name',
                'jel.debit',
                'jel.credit',
                'je.id as entry_id',
            ])
            ->orderBy('je.date')
            ->orderBy('je.id')
            ->orderBy('jel.id');

        if ($this->fromDate) {
            $query->where('je.date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->where('je.date', '<=', $this->toDate);
        }

        $rows = collect();
        $prevEntryId = null;
        $totalDebit  = 0;
        $totalCredit = 0;

        foreach ($query->get() as $line) {
            // Separator row between different journal entries
            if ($prevEntryId !== null && $prevEntryId !== $line->entry_id) {
                $rows->push(['', '', '', '', '', '', '', '']);
            }

            $rows->push([
                $prevEntryId !== $line->entry_id ? $line->date : '',
                $prevEntryId !== $line->entry_id ? $line->entry_no : '',
                $prevEntryId !== $line->entry_id ? $line->entry_desc : '',
                $prevEntryId !== $line->entry_id
                    ? ReferenceTypeMapper::format($line->reference_type, $line->reference_id)
                    : '',
                $line->account_number,
                $line->account_name,
                $line->debit  > 0 ? number_format($line->debit,  2) : '',
                $line->credit > 0 ? number_format($line->credit, 2) : '',
            ]);

            $totalDebit  += (float) $line->debit;
            $totalCredit += (float) $line->credit;
            $prevEntryId  = $line->entry_id;
        }

        // Grand totals
        $rows->push([]);
        $rows->push([
            'الإجمالي', '', '', '', '', '',
            number_format($totalDebit,  2),
            number_format($totalCredit, 2),
        ]);

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 18,
            'C' => 38,
            'D' => 20,
            'E' => 14,
            'F' => 36,
            'G' => 16,
            'H' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['italic' => true, 'color' => ['argb' => 'FF6B7280']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
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
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->setRightToLeft(true);
                $sheet->setAutoFilter('A4:H4');
                $sheet->freezePane('A5');

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A{$lastRow}:H{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF59E0B']],
                ]);
            },
        ];
    }
}
