<?php

namespace App\Exports;

use App\Models\Customer;
use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomerBalanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithCustomCsvSettings
{
    private $rowNumber = 1;
    private $isOperational = false;

    public function __construct(
        private ?int $customerId = null,
        private ?string $fromDate = null,
        private ?string $toDate = null
    ) {}

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }

    public function collection()
    {
        // ─── Single customer detailed statement ──────────────────────────────
        if ($this->customerId) {
            $customer = Customer::findOrFail($this->customerId);
            $fromDate = $this->fromDate ?? now()->startOfMonth()->toDateString();
            $toDate   = $this->toDate ?? now()->toDateString();
            $this->isOperational = $customer->concrete_type === 'operational';

            $orders = $customer->orders()
                ->with('concreteMix')
                ->where('status', '!=', 'cancelled')
                ->whereBetween('delivery_date', [$fromDate, $toDate])
                ->orderBy('delivery_date')
                ->get();

            $payments = $customer->payments()
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->orderBy('payment_date')
                ->get();

            $directTreasuryTxs = \App\Models\TreasuryTransaction::where('reference_type', 'customer')
                ->where('reference_id', $customer->id)
                ->whereBetween('transaction_date', [$fromDate, $toDate])
                ->orderBy('transaction_date')
                ->get();

            $paidCredits = \App\Models\Credit::where('creditable_type', 'customer')
                ->where('creditable_id', $customer->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$fromDate, $toDate])
                ->orderBy('paid_date')
                ->get();

            $totalOrders     = $orders->sum('total_amount');
            $totalCashOrders = $orders->sum('cash_amount');
            $totalCreditPayments = $paidCredits->sum('amount');

            $incomingPaymentsSum = $payments->where('amount', '>', 0)->sum('amount')
                                 + $directTreasuryTxs->where('type', 'in')->sum('amount')
                                 + $totalCreditPayments;

            $outgoingPaymentsSum = abs($payments->where('amount', '<', 0)->sum('amount'))
                                 + $directTreasuryTxs->where('type', 'out')->sum('amount');

            $totalPayments   = $incomingPaymentsSum + $totalCashOrders;
            $balance         = ($totalOrders - $totalCashOrders) + $outgoingPaymentsSum - $incomingPaymentsSum;
            $balanceType     = $balance > 0 ? 'مديون' : ($balance < 0 ? 'دائن' : 'متعادل');
            $totalM3         = $orders->sum('quantity_m3');
            $totalCement     = $orders->sum('cement_deducted');

            // Build chronological event list
            $events = collect();

            foreach ($orders as $order) {
                $events->push((object)[
                    'date'            => $order->delivery_date,
                    'type'            => 'order',
                    'description'     => 'طلب #' . $order->id . ' - ' . ($order->concreteMix?->name ?? 'خرسانة'),
                    'quantity_m3'     => (float)($order->quantity_m3 ?? 0),
                    'unit_price'      => (float)($order->unit_price ?? 0),
                    'debit'           => (float)($order->total_amount ?? 0),
                    'cash_paid'       => (float)($order->cash_amount ?? 0),
                    'order_price'     => (float)(($order->total_amount ?? 0) - ($order->cash_amount ?? 0)),
                    'credit'          => 0,
                    'cement_deducted' => (float)($order->cement_deducted ?? 0),
                ]);
            }

            foreach ($payments as $payment) {
                $amt = (float)$payment->amount;
                if ($amt < 0) {
                    $outAmt = abs($amt);
                    $events->push((object)[
                        'date'            => $payment->payment_date,
                        'type'            => 'outgoing_payment',
                        'description'     => 'سداد ديون (صادر) - ' . ($payment->notes ?: $payment->payment_method),
                        'quantity_m3'     => 0,
                        'unit_price'      => 0,
                        'debit'           => $outAmt,
                        'cash_paid'       => 0,
                        'order_price'     => $outAmt,
                        'credit'          => 0,
                        'cement_deducted' => 0,
                    ]);
                } else {
                    $events->push((object)[
                        'date'            => $payment->payment_date,
                        'type'            => 'payment',
                        'description'     => 'دفعة - ' . ($payment->notes ?: $payment->payment_method),
                        'quantity_m3'     => 0,
                        'unit_price'      => 0,
                        'debit'           => 0,
                        'cash_paid'       => 0,
                        'order_price'     => 0,
                        'credit'          => $amt,
                        'cement_deducted' => 0,
                    ]);
                }
            }

            foreach ($directTreasuryTxs as $tx) {
                $amt = (float)$tx->amount;
                if ($tx->type === 'out') {
                    $events->push((object)[
                        'date'            => $tx->transaction_date,
                        'type'            => 'outgoing_payment',
                        'description'     => '' . ($tx->description ?: 'سداد ديون'),
                        'quantity_m3'     => 0,
                        'unit_price'      => 0,
                        'debit'           => $amt,
                        'cash_paid'       => 0,
                        'order_price'     => $amt,
                        'credit'          => 0,
                        'cement_deducted' => 0,
                    ]);
                } else {
                    $events->push((object)[
                        'date'            => $tx->transaction_date,
                        'type'            => 'payment',
                        'description'     => '' . ($tx->description ?: 'دفعة عميل'),
                        'quantity_m3'     => 0,
                        'unit_price'      => 0,
                        'debit'           => 0,
                        'cash_paid'       => 0,
                        'order_price'     => 0,
                        'credit'          => $amt,
                        'cement_deducted' => 0,
                    ]);
                }
            }

            foreach ($paidCredits as $paidCredit) {
                $events->push((object)[
                    'date'            => $paidCredit->paid_date,
                    'type'            => 'credit_payment',
                    'description'     => 'سداد آجل - ' . ($paidCredit->notes ?? 'دفعة آجلة'),
                    'quantity_m3'     => 0,
                    'unit_price'      => 0,
                    'debit'           => 0,
                    'cash_paid'       => 0,
                    'order_price'     => 0,
                    'credit'          => (float)$paidCredit->amount,
                    'cement_deducted' => 0,
                ]);
            }

            // Sort chronologically
            $sortedEvents = $events->sortBy(fn($e) => \Carbon\Carbon::parse($e->date)->timestamp)->values();

            // Build transactions
            $transactions   = collect();
            $runningBalance = 0;
            $runningCement  = (float)$customer->cement_balance + (float)$totalCement;

            foreach ($sortedEvents as $ev) {
                if ($ev->type === 'order') {
                    $runningBalance += $ev->order_price;
                    $runningCement  -= $ev->cement_deducted;
                } elseif ($ev->type === 'outgoing_payment') {
                    $runningBalance += $ev->debit;
                } else {
                    $runningBalance -= $ev->credit;
                }

                $transactions->push([
                    'date'           => $ev->date,
                    'description'    => $ev->description,
                    'quantity_m3'    => $ev->quantity_m3,
                    'unit_price'     => $ev->unit_price,
                    'debit'          => $ev->debit,
                    'cash_paid'      => $ev->cash_paid,
                    'order_price'    => $ev->order_price,
                    'credit'         => $ev->credit,
                    'balance'        => $runningBalance,
                    'cement_deducted'=> $ev->cement_deducted,
                    'cement_balance' => $runningCement,
                ]);
            }

            // ─── Header block ─────────────────────────────────────────────
            $data = collect([
                ['كشف حساب عميل', '', '', '', '', '', '', '', '', ($this->isOperational ? '' : ''), ($this->isOperational ? '' : '')],
                ['', '', '', '', '', '', '', '', '', '', ''],
                ['العميل',        $customer->name,                '', '', '', '', '', '', '', '', ''],
                ['الهاتف',        $customer->phone ?? '-',        '', '', '', '', '', '', '', '', ''],
                ['العنوان',       $customer->address ?? '-',      '', '', '', '', '', '', '', '', ''],
                ['الموقع',        $customer->location ?? '-',     '', '', '', '', '', '', '', '', ''],
                ['نوع الخرسانة', $customer->concrete_type_label,  '', '', '', '', '', '', '', '', ''],
                ['نوع الدفع',    $customer->payment_type_label,   '', '', '', '', '', '', '', '', ''],
                ['من تاريخ',      $fromDate,                      '', '', '', '', '', '', '', '', ''],
                ['إلى تاريخ',     $toDate,                        '', '', '', '', '', '', '', '', ''],
                ['', '', '', '', '', '', '', '', '', '', ''],
                ['إجمالي الطلبات',    number_format($totalOrders, 2),   '', '', '', '', '', '', '', '', ''],
                ['إجمالي المقبوضات',  number_format($totalPayments, 2), '', '', '', '', '', '', '', '', ''],
                ['الرصيد',            number_format(abs($balance), 2) . ' (' . $balanceType . ')', '', '', '', '', '', '', '', '', ''],
                ['إجمالي الكمية (م³)',number_format($totalM3, 2),       '', '', '', '', '', '', '', '', ''],
            ]);

            if ($this->isOperational) {
                $data->push(['الأسمنت المخصوم (طن)', number_format($totalCement, 3), '', '', '', '', '', '', '', '', '']);
                $data->push(['رصيد الأسمنت الحالي (طن)', number_format($customer->cement_balance, 3), '', '', '', '', '', '', '', '', '']);
            }

            $data->push(['', '', '', '', '', '', '', '', '', '', '']);

            // Column headers
            if ($this->isOperational) {
                $data->push(['التاريخ', 'البيان', 'الكمية م³', 'سعر المتر', 'مبلغ الطلب', 'نقدي فوري', 'آجل', 'سداد', 'الرصيد المالي', 'أسمنت مخصوم (طن)', 'رصيد أسمنت (طن)']);
            } else {
                $data->push(['التاريخ', 'البيان', 'الكمية م³', 'سعر المتر', 'مبلغ الطلب', 'نقدي فوري', 'آجل', 'سداد', 'الرصيد المالي', '', '']);
            }

            $this->rowNumber = $data->count();

            foreach ($transactions as $t) {
                $this->rowNumber++;
                if ($this->isOperational) {
                    $data->push([
                        \Carbon\Carbon::parse($t['date'])->format('Y-m-d'),
                        $t['description'],
                        $t['quantity_m3'] > 0 ? number_format($t['quantity_m3'], 2) : '-',
                        $t['unit_price'] > 0 ? number_format($t['unit_price'], 2) : '-',
                        $t['debit'] > 0 ? number_format($t['debit'], 2) : '-',
                        $t['cash_paid'] > 0 ? number_format($t['cash_paid'], 2) : '-',
                        $t['order_price'] > 0 ? number_format($t['order_price'], 2) : '-',
                        $t['credit'] > 0 ? number_format($t['credit'], 2) : '-',
                        number_format($t['balance'], 2),
                        $t['cement_deducted'] > 0 ? number_format($t['cement_deducted'], 3) : '-',
                        number_format($t['cement_balance'], 3),
                    ]);
                } else {
                    $data->push([
                        \Carbon\Carbon::parse($t['date'])->format('Y-m-d'),
                        $t['description'],
                        $t['quantity_m3'] > 0 ? number_format($t['quantity_m3'], 2) : '-',
                        $t['unit_price'] > 0 ? number_format($t['unit_price'], 2) : '-',
                        $t['debit'] > 0 ? number_format($t['debit'], 2) : '-',
                        $t['cash_paid'] > 0 ? number_format($t['cash_paid'], 2) : '-',
                        $t['order_price'] > 0 ? number_format($t['order_price'], 2) : '-',
                        $t['credit'] > 0 ? number_format($t['credit'], 2) : '-',
                        number_format($t['balance'], 2),
                        '',
                        '',
                    ]);
                }
            }

            return $data;
        }

        // ─── All customers summary ────────────────────────────────────────
        $fromDate = $this->fromDate ?? now()->startOfMonth()->toDateString();
        $toDate   = $this->toDate ?? now()->toDateString();

        $data = (new ReportService())->customerBalanceReport($fromDate, $toDate);

        $mappedData = collect($data)->map(function ($r) {
            $this->rowNumber++;
            return [
                $r['customer']->name,
                $r['customer']->phone ?? '-',
                $r['customer']->address ?? '-',
                $r['customer']->concrete_type_label,
                $r['customer']->payment_type_label,
                $r['order_count'],
                number_format($r['total_concrete_m3'], 2),
                number_format($r['total_orders'], 2),
                number_format($r['total_payments'], 2),
                number_format(abs($r['outstanding']), 2) . ' (' . ($r['outstanding'] > 0 ? 'مديون' : ($r['outstanding'] < 0 ? 'دائن' : 'متعادل')) . ')',
                $r['customer']->concrete_type === 'operational' ? number_format($r['cement_balance'], 3) . ' طن' : '-',
            ];
        });

        return $mappedData;
    }

    public function headings(): array
    {
        if ($this->customerId) {
            return []; // headers are built inside collection
        }
        return [
            'اسم العميل',
            'الهاتف',
            'العنوان',
            'نوع الخرسانة',
            'نوع الدفع',
            'عدد الطلبات',
            'إجمالي الكمية (م³)',
            'إجمالي الطلبات',
            'المدفوعات',
            'الرصيد المتبقي',
            'رصيد الأسمنت (طن)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        $lastCol = $this->isOperational ? 'K' : 'K';
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if ($this->customerId) {
            // Title
            $sheet->mergeCells('A1:K1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

            // Metadata rows bold labels
            $sheet->getStyle('A3:A17')->getFont()->setBold(true);

            // The transaction table header row (find it dynamically)
            $headerRow = $this->isOperational ? 19 : 18;
            $sheet->getStyle("A{$headerRow}:K{$headerRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            if ($this->rowNumber > $headerRow) {
                $dataStart = $headerRow + 1;
                $sheet->getStyle("A{$dataStart}:K{$this->rowNumber}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }
        } else {
            $sheet->getStyle('A1:K1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $rowCount = $this->rowNumber;
            if ($rowCount > 1) {
                $sheet->getStyle("A2:K{$rowCount}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }
        }

        return [];
    }

    public function title(): string
    {
        return $this->customerId ? 'كشف حساب تفصيلي' : 'أرصدة العملاء';
    }
}