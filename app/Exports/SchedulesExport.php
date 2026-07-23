<?php

namespace App\Exports;

use App\Models\WeeklySchedule;
use App\Models\WeeklyScheduleEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class SchedulesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithCustomCsvSettings
{
    private int $rowNumber = 1;

    public function __construct(
        private string $fromDate,
        private string $toDate,
        private ?int $customerId = null,
        private ?string $entryStatus = null
    ) {}

    public function getCsvSettings(): array
    {
        return ['use_bom' => true, 'output_encoding' => 'UTF-8'];
    }

    public function collection(): Collection
    {
        $rows = collect();

        // Meta header
        $rows->push(['تقرير الجداول الأسبوعية', '', '', '', '', '', '', '', '', '']);
        $rows->push(['من تاريخ', $this->fromDate, '', '', '', '', '', '', '', '']);
        $rows->push(['إلى تاريخ', $this->toDate, '', '', '', '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '', '', '', '', '']);
        $this->rowNumber = $rows->count();

        // Build query
        $query = WeeklySchedule::with(['entries' => function ($q) {
            $q->with(['customer', 'order.concreteMix']);
            if ($this->customerId) {
                $q->where('customer_id', $this->customerId);
            }
            if ($this->entryStatus) {
                $q->where('status', $this->entryStatus);
            }
        }, 'createdBy'])
        ->where(function ($q) {
            $q->where('week_start', '<=', $this->toDate)
              ->where('week_end', '>=', $this->fromDate);
        })
        ->orderBy('week_start');

        $schedules = $query->get();

        foreach ($schedules as $schedule) {
            if ($schedule->entries->isEmpty()) continue;

            // Schedule header row
            $this->rowNumber++;
            $rows->push([
                'الأسبوع: ' . $schedule->week_number . ' (' . $schedule->year . ')',
                'من: ' . $schedule->week_start->format('Y-m-d'),
                'إلى: ' . $schedule->week_end->format('Y-m-d'),
                'الحالة: ' . $schedule->status_label,
                'أنشأه: ' . ($schedule->createdBy->name ?? '-'),
                'ملاحظات: ' . ($schedule->notes ?? '-'),
                '', '', '', '',
            ]);

            // Column sub-header
            $this->rowNumber++;
            $rows->push([
                'رقم الطلب', 'العميل', 'موقع التوصيل', 'تاريخ التوصيل', 'وقت التوصيل',
                'الكمية م³', 'نوع الخرسانة', 'الخلطة', 'الحالة', 'ملاحظات المهندس',
            ]);

            foreach ($schedule->entries as $entry) {
                $this->rowNumber++;
                $rows->push([
                    $entry->order_id ? '#' . $entry->order_id : '-',
                    $entry->customer->name ?? '-',
                    $entry->site_location ?? '-',
                    $entry->delivery_date->format('Y-m-d'),
                    $entry->delivery_time ? \Carbon\Carbon::parse($entry->delivery_time)->format('H:i') : '-',
                    number_format($entry->quantity_m3, 2),
                    $entry->order?->concrete_type_label ?? '-',
                    $entry->order?->concreteMix?->name ?? '-',
                    $entry->status_label,
                    $entry->engineer_notes ?? '-',
                ]);
            }

            // Sub-total row
            $this->rowNumber++;
            $rows->push([
                'إجمالي الأسبوع', '', '', '', '',
                number_format($schedule->entries->sum('quantity_m3'), 2),
                '', '',
                'مكتمل: ' . $schedule->entries->where('status', 'completed')->count() .
                ' | معلق: ' . $schedule->entries->where('status', 'pending')->count() .
                ' | ملغي: '  . $schedule->entries->where('status', 'cancelled')->count(),
                '',
            ]);

            // Spacer
            $this->rowNumber++;
            $rows->push(['', '', '', '', '', '', '', '', '', '']);
        }

        // Grand total
        $allEntries = $schedules->flatMap(fn($s) => $s->entries);
        $this->rowNumber++;
        $rows->push([
            'الإجمالي الكلي',
            $schedules->count() . ' أسبوع',
            $allEntries->count() . ' إدخال',
            '',
            '',
            number_format($allEntries->sum('quantity_m3'), 2) . ' م³',
            '',
            '',
            'مكتمل: ' . $allEntries->where('status', 'completed')->count() .
            ' | معلق: '  . $allEntries->where('status', 'pending')->count() .
            ' | ملغي: '   . $allEntries->where('status', 'cancelled')->count(),
            '',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Title
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }

    public function title(): string
    {
        return 'الجداول الأسبوعية';
    }
}
