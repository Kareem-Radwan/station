<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private $fromDate;
    private $toDate;
    private $customerId;
    private $status;
    private $concreteType;
    private $rowNumber = 1;
    private $totals = [];

    public function __construct($fromDate = null, $toDate = null, $customerId = null, $status = null, $concreteType = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->customerId = $customerId;
        $this->status = $status;
        $this->concreteType = $concreteType;
    }

    public function collection()
    {
        $query = Order::with(['customer', 'concreteMix', 'createdBy'])
            ->when($this->fromDate, fn($q, $v) => $q->where('delivery_date', '>=', $v))
            ->when($this->toDate, fn($q, $v) => $q->where('delivery_date', '<=', $v))
            ->when($this->customerId, fn($q, $v) => $q->where('customer_id', $v))
            ->when($this->status, fn($q, $v) => $q->where('status', $v))
            ->when($this->concreteType, fn($q, $v) => $q->where('concrete_type', $v))
            ->orderBy('delivery_date')
            ->orderBy('id')
            ->get();

        // Initialize totals
        $this->totals = [
            'count' => $query->count(),
            'quantity' => 0,
            'cement_deducted' => 0,
            'total_amount' => 0,
            'cash_amount' => 0,
            'credit_amount' => 0,
        ];

        $data = collect();

        foreach ($query as $order) {
            $data->push($order);

            // Add to totals (only for delivered orders)
            if ($order->status === 'delivered') {
                $this->totals['quantity'] += (float)$order->quantity_m3;
                $this->totals['cement_deducted'] += (float)($order->cement_deducted ?? 0);
                $this->totals['total_amount'] += (float)($order->total_amount ?? 0);
                $this->totals['cash_amount'] += (float)($order->cash_amount ?? 0);
                $this->totals['credit_amount'] += (float)($order->total_amount ?? 0) - (float)($order->cash_amount ?? 0);
            }
        }

        // Add totals row
        $data->push((object)[
            'is_total' => true,
            'totals' => $this->totals,
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'رقم الطلب',
            'العميل',
            'رقم الهاتف',
            'نوع الخرسانة',
            'الخلطة',
            'الكمية (م³)',
            'الأسمنت المخصوم',
            'الموقع',
            'تاريخ التسليم',
            'وقت التسليم',
            'سعر الوحدة',
            'المبلغ الإجمالي',
            'دفع نقدي',
            'سعر الطلب (آجل)',
            'نوع الدفع',
            'تاريخ الاستحقاق',
            'الحالة',
            'ملاحظات',
            'تم بواسطة',
            'تاريخ الإنشاء',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        // Handle totals row
        if (isset($row->is_total) && $row->is_total) {
            return [
                'الإجمالي',
                'عدد الطلبات: ' . $row->totals['count'],
                '',
                '',
                '',
                number_format($row->totals['quantity'], 2),
                number_format($row->totals['cement_deducted'], 2),
                '',
                '',
                '',
                '',
                number_format($row->totals['total_amount'], 2),
                number_format($row->totals['cash_amount'], 2),
                number_format($row->totals['credit_amount'], 2),
                '',
                '',
                '',
                '',
                '',
                '',
            ];
        }

        // Regular order row
        return [
            $row->id,
            $row->customer->name ?? '',
            $row->customer->phone ?? '',
            $row->concrete_type === 'operational' ? 'تشغيلية' : 'متكامل',
            $row->concreteMix->name ?? '-',
            number_format($row->quantity_m3, 2),
            number_format($row->cement_deducted ?? 0, 2),
            $row->location ?? '',
            $row->delivery_date->format('Y-m-d'),
            $row->delivery_time ?? '',
            number_format($row->unit_price ?? 0, 2),
            number_format($row->total_amount ?? 0, 2),
            number_format($row->cash_amount ?? 0, 2),
            number_format(($row->total_amount ?? 0) - ($row->cash_amount ?? 0), 2),
            match($row->payment_type) {
                'cash' => 'نقدي',
                'credit' => 'آجل',
                'mixed' => 'مختلط',
                default => '-'
            },
            $row->credit_due_date ? $row->credit_due_date->format('Y-m-d') : '',
            match($row->status) {
                'pending' => 'معلق',
                'scheduled' => 'مجدول',
                'delivered' => 'تم التسليم',
                'cancelled' => 'ملغي',
                default => $row->status
            },
            $row->notes ?? '',
            $row->createdBy->name ?? '',
            $row->created_at->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e293b']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '475569']]],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(25);

        // Apply styles to all data rows
        $rowCount = $this->rowNumber;
        if ($rowCount > 1) {
            $sheet->getStyle("A2:T{$rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Totals row style
            if ($rowCount > 2) {
                $sheet->getStyle("A{$rowCount}:T{$rowCount}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                ]);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'الطلبات';
    }
}
