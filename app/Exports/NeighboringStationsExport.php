<?php

namespace App\Exports;

use App\Models\NeighboringStation;
use App\Models\NeighboringStationTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NeighboringStationsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $stationId;
    protected $fromDate;
    protected $toDate;
    protected $rowNumber = 0;

    public function __construct($stationId = null, $fromDate = null, $toDate = null)
    {
        $this->stationId = $stationId;
        $this->fromDate = $fromDate ?? now()->startOfMonth()->toDateString();
        $this->toDate = $toDate ?? now()->toDateString();
    }

    public function collection()
    {
        if ($this->stationId) {
            // Single station detailed report
            return NeighboringStationTransaction::with(['station', 'recordedBy'])
                ->where('neighboring_station_id', $this->stationId)
                ->whereBetween('transaction_date', [$this->fromDate, $this->toDate])
                ->orderBy('transaction_date')
                ->get();
        } else {
            // All stations summary
            $stations = NeighboringStation::with(['transactions' => function($query) {
                $query->whereBetween('transaction_date', [$this->fromDate, $this->toDate]);
            }])->get();

            $data = collect();
            
            foreach ($stations as $station) {
                $totalIncoming = $station->transactions->where('direction', 'incoming')->sum('amount');
                $totalOutgoing = $station->transactions->where('direction', 'outgoing')->sum('amount');
                $totalPaidIncoming = $station->transactions->where('direction', 'incoming')->sum('paid_amount');
                $totalPaidOutgoing = $station->transactions->where('direction', 'outgoing')->sum('paid_amount');
                $balance = ($totalIncoming - $totalPaidIncoming) - ($totalOutgoing - $totalPaidOutgoing);

                $data->push((object)[
                    'station_name' => $station->name,
                    'transaction_count' => $station->transactions->count(),
                    'total_incoming' => $totalIncoming,
                    'total_outgoing' => $totalOutgoing,
                    'total_paid' => $totalPaidIncoming + $totalPaidOutgoing,
                    'balance' => $balance,
                ]);
            }

            return $data;
        }
    }

    public function headings(): array
    {
        if ($this->stationId) {
            return [
                '#',
                'المحطة',
                'التاريخ',
                'النوع',
                'الاتجاه',
                'الوصف',
                'المبلغ',
                'المدفوع',
                'المتبقي',
                'حالة الدفع',
                'رقم المرجع',
                'المسجل بواسطة',
            ];
        } else {
            return [
                '#',
                'المحطة',
                'عدد المعاملات',
                'إجمالي الوارد',
                'إجمالي الصادر',
                'المدفوع',
                'الرصيد',
                'حالة الرصيد',
            ];
        }
    }

    public function map($row): array
    {
        $this->rowNumber++;

        if ($this->stationId) {
            // Detailed transaction mapping
            return [
                $this->rowNumber,
                $row->station->name,
                $row->transaction_date->format('Y-m-d'),
                $row->transaction_type_label,
                $row->direction_label,
                $row->description,
                number_format($row->amount, 2),
                number_format($row->paid_amount, 2),
                number_format($row->getRemainingAmount(), 2),
                $row->payment_status_label,
                $row->reference_number ?? '-',
                $row->recordedBy?->name ?? '-',
            ];
        } else {
            // Summary mapping
            return [
                $this->rowNumber,
                $row->station_name,
                $row->transaction_count,
                number_format($row->total_incoming, 2),
                number_format($row->total_outgoing, 2),
                number_format($row->total_paid, 2),
                number_format(abs($row->balance), 2),
                $row->balance >= 0 ? 'لصالحنا' : 'علينا',
            ];
        }
    }

    public function title(): string
    {
        return 'المحطات المجاورة';
    }

    public function styles(Worksheet $sheet)
    {
        // RTL direction
        $sheet->setRightToLeft(true);

        // Header styling
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1e3a5f']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Center align all cells
        $sheet->getStyle('A:' . $sheet->getHighestColumn())
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
