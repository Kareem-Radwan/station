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

class AnnualProfitExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowCount = 1;

    public function __construct(private int $year) {}

    public function collection()
    {
        $d = (new ReportService())->annualProfitReport($this->year);
        $collection = collect($d['months'])->map(fn($m) => [
            $m['period'],
            number_format($m['revenue'], 2),
            number_format($m['expenses'], 2),
            number_format($m['profit'], 2)
        ]);

        $this->rowCount += $collection->count();

        return $collection;
    }

    public function headings(): array
    {
        return ['الشهر', 'الإيرادات', 'المصروفات', 'صافي الربح'];
    }

    public function styles(Worksheet $sheet): array
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Data rows style
        if ($this->rowCount > 1) {
            $sheet->getStyle("A2:D{$this->rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'الربح السنوي ' . $this->year;
    }
}
