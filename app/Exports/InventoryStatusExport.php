<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryStatusExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowNumber = 1;

    public function collection()
    {
        return \App\Models\InventoryItem::all()->map(function ($item) {
            $this->rowNumber++;
            return [
                $item->name_ar,
                $item->unit,
                number_format($item->current_stock, 3),
                number_format($item->alert_threshold, 3),
                $item->isBelowAlert() ? 'تحت الحد' : 'طبيعي',
            ];
        });
    }

    public function headings(): array
    {
        return ['المادة', 'الوحدة', 'المخزون الحالي', 'حد التنبيه', 'الحالة'];
    }

    public function styles(Worksheet $sheet)
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

        // Content layout cell styling properties
        $rowCount = $this->rowNumber;
        if ($rowCount > 1) {
            $sheet->getStyle("A2:E{$rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'حالة المخزون';
    }
}