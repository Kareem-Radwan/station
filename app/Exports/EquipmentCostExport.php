<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EquipmentCostExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowNumber = 1;

    public function __construct(private ?string $fromDate = null, private ?string $toDate = null)
    {
    }

    public function collection()
    {
        $fromDate = $this->fromDate ?? now()->startOfMonth()->toDateString();
        $toDate = $this->toDate ?? now()->endOfMonth()->toDateString();

        $data = collect();

        // Owned Equipment
        $equipments = \App\Models\Equipment::all();
        foreach ($equipments as $eq) {
            $fuelCost = (float) $eq->fuelLogs()
                ->whereBetween('log_date', [$fromDate, $toDate])
                ->sum('total_cost');

            $maintCost = (float) $eq->maintenance()
                ->whereBetween('maintenance_date', [$fromDate, $toDate])
                ->sum('cost');

            if ($fuelCost > 0 || $maintCost > 0) {
                $this->rowNumber++;
                $data->push([
                    $eq->name,
                    $eq->type_label ?? $eq->type,
                    'مملوكة',
                    number_format($fuelCost, 2),
                    number_format($maintCost, 2),
                    '-',
                    '-',
                    number_format($fuelCost + $maintCost, 2),
                ]);
            }
        }

        // Rental Cars/Equipment — use actual shift costs
        $rentalContracts = \App\Models\RentalContract::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($rentalContracts as $rental) {
            // Sum actual shift costs in the period
            $rentalShiftCost = (float) DB::table('rental_shifts')
                ->where('rental_contract_id', $rental->id)
                ->whereBetween('shift_date', [$fromDate, $toDate])
                ->sum('total_cost');

            $rentalMaintCost = (float) $rental->maintenance()
                ->whereBetween('maintenance_date', [$fromDate, $toDate])
                ->sum('cost');

            if ($rentalShiftCost > 0 || $rentalMaintCost > 0) {
                $label = $rental->equipment_name;
                if ($rental->car_number)
                    $label .= ' (' . $rental->car_number . ')';
                if ($rental->driver_name)
                    $label .= ' - ' . $rental->driver_name;

                $this->rowNumber++;
                $data->push([
                    $label,
                    'سيارة مستأجرة',
                    'مستأجرة',
                    '-',
                    '-',
                    number_format($rentalShiftCost, 2),
                    number_format($rentalMaintCost, 2),
                    number_format($rentalShiftCost + $rentalMaintCost, 2),
                ]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return ['المعدة / السيارة', 'النوع', 'الفئة', 'وقود', 'صيانة', 'تكلفة الورديات', 'صيانة الإيجار', 'الإجمالي'];
    }

    public function styles(Worksheet $sheet)
    {
        // Set RTL
        $sheet->setRightToLeft(true);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Apply style rules on standard data entries lines
        $rowCount = $this->rowNumber;
        if ($rowCount > 1) {
            $sheet->getStyle("A2:H{$rowCount}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'تكاليف المعدات';
    }
}