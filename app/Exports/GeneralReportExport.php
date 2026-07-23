<?php

namespace App\Exports;

use App\Models\WeeklySchedule;
use App\Models\Contributor;
use App\Services\ReportService;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GeneralReportExport implements WithMultipleSheets
{
    public function __construct(
        private string $fromDate,
        private string $toDate
    ) {}

    public function sheets(): array
    {
        $service = new ReportService();

        return [
            new GeneralReportSummarySheet($this->fromDate, $this->toDate, $service),
            new GeneralReportTreasuryInSheet($this->fromDate, $this->toDate),
            new GeneralReportTreasuryOutSheet($this->fromDate, $this->toDate),
            new GeneralReportCustomersSheet($this->fromDate, $this->toDate, $service),
            new GeneralReportSuppliersSheet($this->fromDate, $this->toDate, $service),
            new GeneralReportInventorySheet($service),
            new GeneralReportOrdersSheet($this->fromDate, $this->toDate),
            new GeneralReportSchedulesSheet($this->fromDate, $this->toDate),
            new GeneralReportPayrollSheet($this->fromDate, $this->toDate),
            new GeneralReportCreditsSheet($service),
            new GeneralReportContributorsSheet(),
        ];
    }
}


// ─── Sheet 1: Executive Summary ──────────────────────────────────────────────
class GeneralReportSummarySheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate, private ReportService $service) {}

    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        $orders = Order::whereBetween('delivery_date', [$this->fromDate, $this->toDate])
            ->where('status', '!=', 'cancelled')->get();

        $revenue   = $orders->sum('total_amount');
        $cashIn    = TreasuryTransaction::where('type', 'in')
            ->whereBetween('transaction_date', [$this->fromDate, $this->toDate])->sum('amount');
        $cashOut   = TreasuryTransaction::where('type', 'out')
            ->whereBetween('transaction_date', [$this->fromDate, $this->toDate])->sum('amount');
        $treasury  = TreasuryTransaction::orderBy('transaction_date', 'desc')->value('balance_after') ?? 0;
        $netProfit = $cashIn - $cashOut;

        $suppliers = $this->service->supplierBalanceReport($this->fromDate, $this->toDate);
        $customers = $this->service->customerBalanceReport($this->fromDate, $this->toDate);

        return collect([
            ['التقرير العام الشامل - محطة الخرسانة', ''],
            ['الفترة', $this->fromDate . ' إلى ' . $this->toDate],
            ['تاريخ الإصدار', now()->format('Y-m-d H:i')],
            ['', ''],
            ['[ الملخص المالي ]', ''],
            ['إجمالي مبيعات الخرسانة (طلبات)', number_format($revenue, 2)],
            ['الوارد للخزينة خلال الفترة', number_format($cashIn, 2)],
            ['إجمالي المصروفات (خزينة)', number_format($cashOut, 2)],
            ['صافي الربح (وارد - صادر)', number_format($netProfit, 2)],
            ['رصيد الخزينة الحالي', number_format($treasury, 2)],
            ['', ''],
            ['[ ملخص الطلبات ]', ''],
            ['عدد الطلبات', $orders->count()],
            ['إجمالي الكمية (م³)', number_format($orders->sum('quantity_m3'), 2)],
            ['إجمالي المبلغ', number_format($orders->sum('total_amount'), 2)],
            ['المحصل نقدي', number_format($orders->sum('cash_amount'), 2)],
            ['الآجل غير المحصل', number_format($orders->sum('total_amount') - $orders->sum('cash_amount'), 2)],
            ['', ''],
            ['[ ملخص العملاء ]', ''],
            ['عدد العملاء النشطين', $customers->count()],
            ['إجمالي ما على العملاء (مديونية)', number_format($customers->sum('outstanding'), 2)],
            ['', ''],
            ['[ ملخص الموردين ]', ''],
            ['عدد الموردين', $suppliers->count()],
            ['إجمالي المشتريات', number_format($suppliers->sum('total_purchases'), 2)],
            ['إجمالي ما نستحق للموردين', number_format($suppliers->sum('outstanding'), 2)],
        ]);

    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        return [];
    }

    public function title(): string { return 'الملخص التنفيذي'; }
}


// ─── Sheet 1b: Treasury IN (وارد للخزينة) ────────────────────────────────────
class GeneralReportTreasuryInSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        $rows = \Illuminate\Support\Facades\DB::table('treasury_transactions')
            ->where('type', 'in')
            ->whereBetween('transaction_date', [$this->fromDate, $this->toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $categoryLabels = [
            'customer_payment'   => 'دفعة عميل',
            'supplier_payment'   => 'دفعة مورد',
            'payroll'            => 'رواتب',
            'salary'             => 'راتب',
            'expense'            => 'مصروف',
            'land_rent'          => 'إيجار أرض',
            'equipment_rent'     => 'إيجار معدة',
            'rental'             => 'إيجار',
            'maintenance'        => 'صيانة',
            'fuel'               => 'وقود',
            'contributor_payment'        => 'دفعة مساهم',
            'employee_borrow_repayment'        => 'سلفة موظف',
            'neighboring_station_incoming'        => 'وارد محطات مجاورة',
            'neighboring_station_outgoing'        => 'صادر محطات مجاورة',
            'other'              => 'أخرى',
            'income'             => 'إيراد',
            'refund'             => 'استرداد',
            'transfer'           => 'تحويل',
            'deposit'            => 'إيداع',
            'withdrawal'         => 'سحب',
            'order_payment'      => 'دفعة طلب',
            'advance'            => 'سلفة',
            'credit_payment'     => 'سداد دين',
            'purchase'           => 'مشتريات',
            'purchase_payment'   => 'دفعة مشتريات',
        ];

        $mapped = $rows->map(fn($tx) => [
            $tx->transaction_date,
            $tx->description ?? '-',
            $categoryLabels[$tx->category] ?? $tx->category,
            number_format($tx->amount, 2),
            number_format($tx->balance_after, 2),
        ]);

        // Totals row
        $mapped->push([
            'الإجمالي',
            '',
            '',
            number_format($rows->sum('amount'), 2),
            '',
        ]);

        return $mapped;
    }

    public function headings(): array
    {
        return ['التاريخ', 'البيان', 'التصنيف', 'المبلغ الوارد', 'الرصيد بعد العملية'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'الوارد للخزينة'; }
}


// ─── Sheet 1c: Treasury OUT (صادر من الخزينة) ────────────────────────────────────
class GeneralReportTreasuryOutSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        $rows = \Illuminate\Support\Facades\DB::table('treasury_transactions')
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$this->fromDate, $this->toDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $categoryLabels = [
            'customer_payment'    => 'دفعة من عميل',
            'supplier_payment'    => 'دفعة لمورد',
            'inventory_purchase' => 'شراء مخزون',
            'inventory_sale'     => 'بيع مخزون',
            'receipt_in'          => 'سند قبض',
            'material_cost'         => 'تكلفة المواد',
            'receipt_out'         => 'سند صرف',
            'rental'              => 'مصاريف إيجار',
            'expense'             => 'مصروفات عامة',
            'contributor_payment_out'             => 'دفعة لمساهم',
            'credit_payment'      => 'سداد ديون',
            'rental_maintenance'  => 'صيانة المعدات المستأجرة',
            'vehicle_equipment'   => 'مصاريف مركبات ومعدات',
            'plant_maintenance'   => 'صيانة المحطة وقطع الغيار',
            'salary'            => 'الرواتب',
            'overtime'            => 'العمل الإضافي',
            'employee_deductions' => 'خصومات الموظفين',
            'employee_borrow'     => 'سلفة موظف',
            'employee_borrow_repayment' => 'سداد سلفة موظف',
            'contributor_payment' => 'دفعة من مساهم',
            'employee_borrow_return' => 'إلغاء سلفة موظف',
            'land_rent'           => 'إيجار الأرض',
            'payroll'            => 'رواتب',
            'equipment_rent'     => 'إيجار معدة',
            'maintenance'        => 'صيانة',
            'fuel'               => 'وقود',
            'other'              => 'أخرى',
            'income'             => 'إيراد',
            'refund'             => 'استرداد',
            'transfer'           => 'تحويل',
            'deposit'            => 'إيداع',
            'withdrawal'         => 'سحب',
            'order_payment'      => 'دفعة طلب',
            'advance'            => 'سلفة',
            'purchase'           => 'مشتريات',
            'purchase_payment'   => 'دفعة مشتريات',
            'sales'              => 'مبيعات',
            'expenses'           => 'مصروفات',
        ];

        $mapped = $rows->map(fn($tx) => [
            $tx->transaction_date,
            $tx->description ?? '-',
            $categoryLabels[$tx->category] ?? $tx->category,
            number_format($tx->amount, 2),
            number_format($tx->balance_after, 2),
        ]);

        // Totals row
        $mapped->push([
            'الإجمالي',
            '',
            '',
            number_format($rows->sum('amount'), 2),
            '',
        ]);

        return $mapped;
    }

    public function headings(): array
    {
        return ['التاريخ', 'البيان', 'التصنيف', 'المبلغ الصادر', 'الرصيد بعد العملية'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'الصادر من الخزينة'; }
}


// ─── Sheet 2: Customers ───────────────────────────────────────────────────────
class GeneralReportCustomersSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate, private ReportService $service) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        return collect($this->service->customerBalanceReport($this->fromDate, $this->toDate))->map(fn($r) => [
            $r['customer']->name,
            $r['customer']->phone ?? '-',
            $r['customer']->concrete_type_label,
            $r['customer']->payment_type_label,
            $r['order_count'],
            number_format($r['total_concrete_m3'], 2),
            number_format($r['total_orders'], 2),
            number_format($r['total_payments'], 2),
            number_format(abs($r['outstanding']), 2) . ' (' . ($r['outstanding'] > 0 ? 'مديون' : ($r['outstanding'] < 0 ? 'دائن' : 'متعادل')) . ')',
            $r['customer']->concrete_type === 'operational' ? number_format($r['cement_balance'], 3) : '-',
        ]);
    }

    public function headings(): array
    {
        return ['العميل', 'الهاتف', 'نوع الخرسانة', 'نوع الدفع', 'عدد الطلبات',
                'الكمية م³', 'إجمالي الطلبات', 'المدفوعات', 'الرصيد', 'رصيد الأسمنت (طن)'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'J') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'أرصدة العملاء'; }
}


// ─── Sheet 3: Suppliers ───────────────────────────────────────────────────────
class GeneralReportSuppliersSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate, private ReportService $service) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        return collect($this->service->supplierBalanceReport($this->fromDate, $this->toDate))->map(fn($r) => [
            $r['supplier']->name,
            $r['supplier']->phone ?? '-',
            is_array($r['supplier']->materials) ? implode('، ', $r['supplier']->materials) : '-',
            $r['supplier']->payment_type_label,
            number_format($r['total_purchases'], 2),
            number_format($r['total_payments'], 2),
            number_format($r['outstanding'], 2),
        ]);
    }

    public function headings(): array
    {
        return ['المورد', 'الهاتف', 'المواد', 'نوع الدفع', 'إجمالي المشتريات', 'المدفوعات', 'الرصيد'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'أرصدة الموردين'; }
}


// ─── Sheet 4: Inventory ───────────────────────────────────────────────────────
class GeneralReportInventorySheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private ReportService $service) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        return collect($this->service->inventoryStatusReport())->map(fn($r) => [
            $r['item']->name,
            $r['item']->unit,
            number_format($r['current_stock'], 2),
            number_format($r['threshold'], 2),
            $r['is_low'] ? 'منخفض ⚠️' : 'جيد',
        ]);
    }

    public function headings(): array
    {
        return ['المادة', 'الوحدة', 'الرصيد الحالي', 'الحد الأدنى', 'الحالة'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'المخزون'; }
}


// ─── Sheet 5: Orders ─────────────────────────────────────────────────────────
class GeneralReportOrdersSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        return Order::with(['customer', 'concreteMix'])
            ->whereBetween('delivery_date', [$this->fromDate, $this->toDate])
            ->where('status', '!=', 'cancelled')
            ->orderBy('delivery_date')
            ->get()
            ->map(fn($o) => [
                '#' . $o->id,
                $o->delivery_date->format('Y-m-d'),
                $o->delivery_time ?? '-',
                $o->customer->name,
                $o->concrete_type_label,
                $o->concreteMix?->name ?? '-',
                number_format($o->quantity_m3, 2),
                number_format($o->cement_deducted ?? 0, 3),
                $o->location ?? '-',
                number_format($o->total_amount ?? 0, 2),
                number_format($o->cash_amount ?? 0, 2),
                number_format(($o->total_amount ?? 0) - ($o->cash_amount ?? 0), 2),
                $o->payment_type_label,
                $o->status_label,
                $o->notes ?? '-',
            ]);
    }

    public function headings(): array
    {
        return ['#', 'التاريخ', 'وقت التسليم', 'العميل', 'نوع الخرسانة', 'الخلطة',
                'الكمية م³', 'الأسمنت طن', 'الموقع', 'المبلغ', 'نقدي', 'آجل',
                'نوع الدفع', 'الحالة', 'ملاحظات'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'O') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'الطلبات'; }
}


// ─── Sheet 6: Schedules ───────────────────────────────────────────────────────
class GeneralReportSchedulesSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        $rows = collect();
        WeeklySchedule::with(['entries.customer', 'entries.order.concreteMix'])
            ->where('week_start', '<=', $this->toDate)
            ->where('week_end', '>=', $this->fromDate)
            ->orderBy('week_start')
            ->get()
            ->each(function ($schedule) use ($rows) {
                foreach ($schedule->entries as $entry) {
                    $rows->push([
                        'أسبوع ' . $schedule->week_number . ' (' . $schedule->year . ')',
                        $schedule->week_start->format('Y-m-d'),
                        $schedule->week_end->format('Y-m-d'),
                        $schedule->status_label,
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
            });

        return $rows;
    }

    public function headings(): array
    {
        return ['الأسبوع', 'بداية الأسبوع', 'نهاية الأسبوع', 'حالة الجدول',
                'رقم الطلب', 'العميل', 'موقع التوصيل', 'تاريخ التوصيل', 'وقت التوصيل',
                'الكمية م³', 'نوع الخرسانة', 'الخلطة', 'حالة الإدخال', 'ملاحظات المهندس'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'N') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'الجداول الأسبوعية'; }
}


// ─── Sheet 7: Payroll ─────────────────────────────────────────────────────────
class GeneralReportPayrollSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private string $fromDate, private string $toDate) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        $from = \Carbon\Carbon::parse($this->fromDate);
        $to   = \Carbon\Carbon::parse($this->toDate);

        return Payroll::with('employee')
            ->where('period_year', '>=', $from->year)
            ->where('period_year', '<=', $to->year)
            ->get()
            ->map(fn($p) => [
                $p->employee->name ?? '-',
                $p->period_month . '/' . $p->period_year,
                number_format($p->base_salary ?? 0, 2),
                number_format($p->overtime_pay ?? 0, 2),
                number_format($p->deductions ?? 0, 2),
                number_format($p->net_salary ?? 0, 2),
                $p->status ?? '-',
            ]);
    }

    public function headings(): array
    {
        return ['الموظف', 'الفترة', 'الراتب الأساسي', 'الإضافي', 'الخصومات', 'صافي الراتب', 'الحالة'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'الرواتب'; }
}


// ─── Sheet 8: Credits ────────────────────────────────────────────────────────
class GeneralReportCreditsSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function __construct(private ReportService $service) {}
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        return collect($this->service->dueCreditReport())->map(fn($r) => [
            $r['party']?->name ?? '-',
            $r['party_type'],
            number_format($r['credit']->amount, 2),
            \Carbon\Carbon::parse($r['credit']->due_date)->format('Y-m-d'),
            $r['credit']->status === 'overdue' ? 'متأخر' : 'مستحق',
            $r['days_left'] > 0 ? 'متبقي ' . abs(ceil($r['days_left'])) . ' يوم' : 'تأخر ' . abs(ceil($r['days_left'])) . ' يوم',
        ]);
    }

    public function headings(): array
    {
        return ['الطرف', 'نوع الطرف', 'المبلغ', 'تاريخ الاستحقاق', 'الحالة', 'الأيام'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'F') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'الديون المستحقة'; }
}


// ─── Sheet 9: Contributors ───────────────────────────────────────────────────
class GeneralReportContributorsSheet implements
    \Maatwebsite\Excel\Concerns\FromCollection,
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithCustomCsvSettings
{
    public function getCsvSettings(): array { return ['use_bom' => true, 'output_encoding' => 'UTF-8']; }

    public function collection()
    {
        return Contributor::with('payments')->get()->map(function ($c) {
            $paid = $c->payments->sum('amount');
            $remaining = $c->share_amount - $paid;
            $pct = $c->share_amount > 0 ? ($paid / $c->share_amount) * 100 : 0;

            return [
                $c->name,
                $c->phone ?? '-',
                $c->national_id ?? '-',
                $c->share_percentage . '%',
                number_format($c->share_amount, 2),
                number_format($paid, 2),
                number_format($remaining, 2),
                number_format($pct, 1) . '%',
            ];
        });
    }

    public function headings(): array
    {
        return ['المساهم', 'الهاتف', 'رقم الهوية', 'نسبة الحصة',
                'قيمة الحصة', 'المدفوع', 'المتبقي', 'نسبة السداد'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        foreach (range('A', 'H') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        return [];
    }

    public function title(): string { return 'المساهمون'; }
}
