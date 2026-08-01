<?php

namespace App\Accounting\Exports;

use App\Accounting\Reports\BalanceSheetReport;
use App\Accounting\Reports\IncomeStatementReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Two-sheet export:
 *   Sheet 1 — Balance Sheet
 *   Sheet 2 — Income Statement
 */
class BalanceSheetExport implements WithMultipleSheets
{
    public function __construct(
        private readonly BalanceSheetReport   $balanceSheet,
        private readonly IncomeStatementReport $incomeStatement,
        private readonly ?string $fromDate = null,
        private readonly ?string $toDate   = null,
    ) {}

    public function sheets(): array
    {
        return [
            new BalanceSheetSheet($this->balanceSheet, $this->fromDate, $this->toDate),
            new IncomeStatementSheet($this->incomeStatement, $this->fromDate, $this->toDate),
        ];
    }
}

// ─── Balance Sheet Sheet ──────────────────────────────────────────────────────

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

class BalanceSheetSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    private array $data;

    public function __construct(
        private readonly BalanceSheetReport $report,
        private readonly ?string $fromDate,
        private readonly ?string $toDate,
    ) {
        $this->data = $report->generate($fromDate, $toDate);
    }

    public function title(): string { return 'الميزانية العمومية'; }

    public function headings(): array
    {
        return [
            ['الميزانية العمومية — نظام القيد المزدوج'],
            ['الفترة: ' . ($this->fromDate ?? 'البداية') . ' → ' . ($this->toDate ?? now()->toDateString())],
            [],
            ['القسم', 'رقم الحساب', 'اسم الحساب', 'الرصيد'],
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        // Assets
        $rows->push(['الأصول', '', '', '']);
        foreach ($this->data['assets'] as $row) {
            $rows->push(['', $row->account_number, $row->account_name, number_format($row->net_balance, 2)]);
        }
        $rows->push(['إجمالي الأصول', '', '', number_format($this->data['total_assets'], 2)]);
        $rows->push([]);

        // Liabilities
        $rows->push(['الخصوم', '', '', '']);
        foreach ($this->data['liabilities'] as $row) {
            $rows->push(['', $row->account_number, $row->account_name, number_format($row->net_balance, 2)]);
        }
        $rows->push(['إجمالي الخصوم', '', '', number_format($this->data['total_liabilities'], 2)]);
        $rows->push([]);

        // Equity
        $rows->push(['حقوق الملكية', '', '', '']);
        foreach ($this->data['equity'] as $row) {
            $rows->push(['', $row->account_number, $row->account_name, number_format($row->net_balance, 2)]);
        }
        $rows->push(['', '', 'صافي الدخل', number_format($this->data['net_income'], 2)]);
        $rows->push(['إجمالي حقوق الملكية', '', '', number_format($this->data['total_equity_with_income'], 2)]);
        $rows->push([]);

        $rows->push(['إجمالي الخصوم + حقوق الملكية', '', '', number_format($this->data['total_liabilities_equity'], 2)]);
        $rows->push(['الميزانية', '', '', $this->data['is_balanced'] ? 'متوازنة ✓' : 'غير متوازنة ✗']);

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 14, 'C' => 40, 'D' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7C3AED']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:D1');
                $event->sheet->getDelegate()->mergeCells('A2:D2');
                $event->sheet->getDelegate()->setRightToLeft(true);
            },
        ];
    }
}

// ─── Income Statement Sheet ───────────────────────────────────────────────────

class IncomeStatementSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    private array $data;

    public function __construct(
        private readonly IncomeStatementReport $report,
        private readonly ?string $fromDate,
        private readonly ?string $toDate,
    ) {
        $this->data = $report->generate($fromDate, $toDate);
    }

    public function title(): string { return 'قائمة الدخل'; }

    public function headings(): array
    {
        return [
            ['قائمة الدخل (الأرباح والخسائر) — نظام القيد المزدوج'],
            ['الفترة: ' . $this->data['period']],
            [],
            ['القسم', 'رقم الحساب', 'اسم الحساب', 'المبلغ'],
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        $rows->push(['الإيرادات', '', '', '']);
        foreach ($this->data['revenue'] as $row) {
            $rows->push(['', $row->account_number, $row->account_name, number_format($row->net_balance, 2)]);
        }
        $rows->push(['إجمالي الإيرادات', '', '', number_format($this->data['total_revenue'], 2)]);
        $rows->push([]);

        $rows->push(['المصروفات', '', '', '']);
        foreach ($this->data['expenses'] as $row) {
            $rows->push(['', $row->account_number, $row->account_name, number_format($row->net_balance, 2)]);
        }
        $rows->push(['إجمالي المصروفات', '', '', number_format($this->data['total_expenses'], 2)]);
        $rows->push([]);

        $rows->push([
            ($this->data['is_profitable'] ? 'صافي الربح' : 'صافي الخسارة'),
            '',
            '',
            number_format(abs($this->data['net_income']), 2),
        ]);

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 14, 'C' => 40, 'D' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:D1');
                $event->sheet->getDelegate()->mergeCells('A2:D2');
                $event->sheet->getDelegate()->setRightToLeft(true);
            },
        ];
    }
}
