<?php

namespace App\Exports;

use App\Models\RentalShift;
use App\Models\RentalContract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RentalShiftsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private $fromDate;
    private $toDate;
    private $rowNumber = 1;
    private $totals = [];

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate ?? now()->startOfMonth()->toDateString();
        $this->toDate = $toDate ?? now()->toDateString();
    }

    public function collection()
    {
        // Get all active rental contracts
        $contracts = RentalContract::with(['supplier', 'shifts' => function ($query) {
            $query->whereBetween('shift_date', [$this->fromDate, $this->toDate])
                ->orderBy('shift_date');
        }])
            ->where('status', '!=', 'cancelled')
            ->orderBy('equipment_name')
            ->get();

        $data = collect();
        
        // Initialize totals
        $this->totals = [
            'hours' => 0,
            'hours_cost' => 0,
            'gratuities' => 0,
            'cards_cost' => 0,
            'driver_allowance' => 0,
            'fuel_cost' => 0,
            'total_cost' => 0,
        ];

        foreach ($contracts as $contract) {
            if ($contract->shifts->isEmpty()) {
                continue;
            }

            // Add contract header
            $data->push((object)[
                'type' => 'contract_header',
                'contract' => $contract,
            ]);

            // Add each shift
            foreach ($contract->shifts as $shift) {
                $data->push((object)[
                    'type' => 'shift',
                    'shift' => $shift,
                    'contract' => $contract,
                ]);

                // Add to totals
                $this->totals['hours'] += (float)$shift->hours;
                $this->totals['hours_cost'] += (float)$shift->hours_cost;
                $this->totals['gratuities'] += (float)$shift->gratuities;
                $this->totals['cards_cost'] += (float)$shift->cards_cost;
                $this->totals['driver_allowance'] += (float)$shift->driver_allowance;
                $this->totals['fuel_cost'] += (float)($shift->fuel_cost ?? 0);
                $this->totals['total_cost'] += (float)$shift->total_cost;
            }

            // Add contract summary
            $contractTotals = [
                'hours' => $contract->shifts->sum('hours'),
                'hours_cost' => $contract->shifts->sum('hours_cost'),
                'gratuities' => $contract->shifts->sum('gratuities'),
                'cards_cost' => $contract->shifts->sum('cards_cost'),
                'driver_allowance' => $contract->shifts->sum('driver_allowance'),
                'fuel_cost' => $contract->shifts->sum('fuel_cost'),
                'total_cost' => $contract->shifts->sum('total_cost'),
            ];

            $data->push((object)[
                'type' => 'contract_summary',
                'contract' => $contract,
                'totals' => $contractTotals,
            ]);
        }

        // Add grand total
        $data->push((object)[
            'type' => 'grand_total',
            'totals' => $this->totals,
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'اسم السيارة',
            'رقم السيارة',
            'السائق',
            'المورد',
            'سعر الساعة',
            'الساعات',
            'تكلفة الساعات',
            'اكراميات',
            'كارتات',
            'معيشة السواق',
            'الوقود',
            'الإجمالي',
            'ملاحظات',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        if ($row->type === 'contract_header') {
            return [
                '',
                $row->contract->equipment_name,
                $row->contract->car_number ?? '',
                $row->contract->driver_name ?? '',
                $row->contract->supplier->name ?? '',
                $row->contract->hourly_price ? number_format($row->contract->hourly_price, 2) : '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
        }

        if ($row->type === 'shift') {
            return [
                $row->shift->shift_date->format('Y-m-d'),
                '',
                '',
                '',
                '',
                $row->contract->hourly_price ? number_format($row->contract->hourly_price, 2) : '-',
                number_format($row->shift->hours, 2),
                number_format($row->shift->hours_cost, 2),
                number_format($row->shift->gratuities, 2),
                number_format($row->shift->cards_cost, 2),
                number_format($row->shift->driver_allowance, 2),
                number_format($row->shift->fuel_cost ?? 0, 2),
                number_format($row->shift->total_cost, 2),
                $row->shift->notes ?? '',
            ];
        }

        if ($row->type === 'contract_summary') {
            return [
                'إجمالي ' . $row->contract->equipment_name,
                '',
                '',
                '',
                '',
                '',
                number_format($row->totals['hours'], 2),
                number_format($row->totals['hours_cost'], 2),
                number_format($row->totals['gratuities'], 2),
                number_format($row->totals['cards_cost'], 2),
                number_format($row->totals['driver_allowance'], 2),
                number_format($row->totals['fuel_cost'], 2),
                number_format($row->totals['total_cost'], 2),
                '',
            ];
        }

        if ($row->type === 'grand_total') {
            return [
                'الإجمالي الكلي',
                '',
                '',
                '',
                '',
                '',
                number_format($row->totals['hours'], 2),
                number_format($row->totals['hours_cost'], 2),
                number_format($row->totals['gratuities'], 2),
                number_format($row->totals['cards_cost'], 2),
                number_format($row->totals['driver_allowance'], 2),
                number_format($row->totals['fuel_cost'], 2),
                number_format($row->totals['total_cost'], 2),
                '',
            ];
        }

        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Apply styles to all data rows
        $rowCount = $this->rowNumber;
        if ($rowCount > 1) {
            $sheet->getStyle("A2:N{$rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'ورديات السيارات';
    }
}