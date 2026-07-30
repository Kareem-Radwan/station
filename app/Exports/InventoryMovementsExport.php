<?php

namespace App\Exports;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryMovementsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowNumber = 1;
    private $item;

    public function __construct(InventoryItem $item)
    {
        $this->item = $item;
    }

    public function collection()
    {
        $movements = InventoryMovement::where('inventory_item_id', $this->item->id)
            ->with(['supplier', 'purchase'])
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return $movements->map(function ($m) {
            $this->rowNumber++;
            
            // Determine invoice number
            $invoiceNumber = '-';
            if ($m->invoice_number) {
                $invoiceNumber = $m->invoice_number;
            } elseif ($m->reference_type === 'purchase' && $m->purchase) {
                $invoiceNumber = $m->purchase->invoice_number ?? '#' . $m->purchase->id;
            }

            // Determine supplier/customer name
            $supplierName = '-';
            if ($m->reference_type === 'customer' && $m->reference_id) {
                // Load customer manually when needed
                $customer = \App\Models\Customer::find($m->reference_id);
                if ($customer) {
                    $supplierName = $customer->name;
                }
            } elseif ($m->supplier) {
                $supplierName = $m->supplier->name;
            }

            return [
                $m->movement_date->format('Y-m-d'),
                $invoiceNumber,
                $m->type === 'in' ? 'وارد' : 'صادر',
                number_format($m->quantity, 3),
                number_format($m->balance_after, 3),
                $m->unit_cost ? number_format($m->unit_cost, 2) : '-',
                $m->total_cost ? number_format($m->total_cost, 2) : '-',
                $supplierName,
                $m->notes ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'رقم الفاتورة',
            'النوع',
            'الكمية (' . $this->item->unit . ')',
            'الرصيد بعد',
            'سعر الوحدة',
            'التكلفة الإجمالية',
            'المورد/العميل',
            'ملاحظات',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Content cell styling
        $rowCount = $this->rowNumber;
        if ($rowCount > 1) {
            $sheet->getStyle("A2:I{$rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'حركات ' . $this->item->name_ar;
    }
}
