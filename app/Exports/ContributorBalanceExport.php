<?php

namespace App\Exports;

use App\Models\Contributor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributorBalanceExport implements FromCollection, WithStyles, WithTitle, ShouldAutoSize
{
    private int $tableHeaderRow = 12;
    private int $lastRow = 12;
    private int $transactionStartRow = 0;
    private int $transactionEndRow = 0;
    private Collection $transactions;

    public function __construct(
        private int $contributorId,
        private ?string $fromDate,
        private ?string $toDate
    ) {
    }

    public function collection()
    {
        $contributor = Contributor::findOrFail($this->contributorId);

        // Get all contributor payments in the period
        $allPayments = $contributor->payments()
            ->whereBetween('payment_date', [$this->fromDate, $this->toDate])
            ->orderBy('payment_date')
            ->get();

        // Separate payments INTO business (contributor pays) vs OUT (we pay contributor)
        $paymentsIn = $allPayments->filter(fn($p) => $p->treasury_transaction_id !== null);
        $paymentsOut = $allPayments->filter(fn($p) => $p->treasury_transaction_id === null);

        $totalPaid = $paymentsOut->sum('amount'); // What we paid to contributor
        $totalReceived = $paymentsIn->sum('amount'); // What contributor paid in

        $shareAmount = $totalReceived; // قيمة الحصة الإجمالية = all contributions (payments in)
        $remaining = $totalReceived - $totalPaid; // المتبقي = contributions - payments out

        /*
        |--------------------------------------------------------------------------
        | Build Transactions
        |--------------------------------------------------------------------------
        */

        $transactions = collect();

        foreach ($allPayments as $payment) {
            $isPaymentOut = $payment->treasury_transaction_id === null;

            $method = match ($payment->payment_method) {
                'cash' => 'نقدي',
                'bank_transfer' => 'تحويل بنكي',
                'check' => 'شيك',
                default => $payment->payment_method,
            };

            $transactions->push([
                'date' => Carbon::parse($payment->payment_date)->format('d/m/Y'),
                'description' => $isPaymentOut ? 'دفعة للمساهم (صادر)' : 'دفعة من المساهم (وارد)',
                'type' => $isPaymentOut ? 'out' : 'in',
                'amount' => number_format($payment->amount, 2),
                'method' => $method,
                'reference_number' => $payment->reference_number ?: '-',
                'notes' => $payment->notes ?: '-',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Excel Rows
        |--------------------------------------------------------------------------
        */

        $rows = new Collection();

        // ============================
        // Title
        // ============================

        $rows->push(['كشف حساب مساهم']);
        $rows->push(['']);

        // ============================
        // Contributor Information
        // ============================

        $rows->push(['اسم المساهم', $contributor->name]);
        $rows->push(['الهاتف', $contributor->phone ?: '-']);
        $rows->push(['رقم الهوية', $contributor->national_id ?: '-']);
        $rows->push(['العنوان', $contributor->address ?: '-']);
        $rows->push(['نسبة الحصة', $contributor->share_percentage . '%']);
        $rows->push(['الملاحظات', $contributor->notes ?: '-']);

        $rows->push(['']);

        // ============================
        // Summary
        // ============================

        $rows->push([
            'قيمة الحصة',
            number_format($shareAmount, 2),
            'إجمالي المدفوع له',
            number_format($totalPaid, 2),
            'إجمالي المستلم منه',
            number_format($totalReceived, 2),
            'المتبقي',
            number_format($remaining, 2),
        ]);

        $rows->push(['']);

        // ============================
        // Table Header
        // ============================

        $this->tableHeaderRow = $rows->count() + 1;

        $rows->push([
            'التاريخ',
            'البيان',
            'النوع',
            'المبلغ',
            'طريقة الدفع',
            'رقم المرجع',
            'ملاحظات',
        ]);

        // ============================
        // Transactions
        // ============================

        $transactionStartRow = $rows->count() + 1;

        foreach ($transactions as $transaction) {
            $rows->push([
                $transaction['date'],
                $transaction['description'],
                $transaction['type'] === 'out' ? 'دفعة للمساهم' : 'دفعة من المساهم',
                $transaction['amount'],
                $transaction['method'],
                $transaction['reference_number'],
                $transaction['notes'],
            ]);
        }

        $transactionEndRow = $rows->count();

        // Store for styling
        $this->transactionStartRow = $transactionStartRow;
        $this->transactionEndRow = $transactionEndRow;
        $this->transactions = $transactions;

        // ============================
        // Total
        // ============================

        if ($transactions->count()) {
            $rows->push([
                '',
                '',
                'إجمالي المتبقي',
                number_format($remaining, 2),
                '',
                '',
                '',
            ]);
        }

        $this->lastRow = $rows->count();

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(true);

        /*
        |--------------------------------------------------------------------------
        | Default Alignment
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("A1:G{$this->lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        /*
        |--------------------------------------------------------------------------
        | Title
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:G1');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18,
                'color' => [
                    'rgb' => 'FFFFFF',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '1E293B',
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Contributor Information
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A3:A8')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E5E7EB',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        $sheet->getStyle('B3:B8')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Summary Row
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A10:H10')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FEF3C7',
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Table Header
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("A{$this->tableHeaderRow}:G{$this->tableHeaderRow}")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '334155',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */

        if ($this->lastRow > $this->tableHeaderRow) {

            $sheet->getStyle(
                'A' . ($this->tableHeaderRow + 1) .
                ':G' . $this->lastRow
            )->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Apply color coding to transaction rows based on type
            $currentRow = $this->transactionStartRow;
            foreach ($this->transactions as $transaction) {
                if ($transaction['type'] === 'out') {
                    // Red background for payments OUT (to contributor)
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'FEE2E2', // light red
                            ],
                        ],
                    ]);
                    // Red text for amount
                    $sheet->getStyle("D{$currentRow}")->applyFromArray([
                        'font' => [
                            'color' => [
                                'rgb' => 'DC2626', // red
                            ],
                            'bold' => true,
                        ],
                    ]);
                } else {
                    // Green background for payments IN (from contributor)
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'D1FAE5', // light green
                            ],
                        ],
                    ]);
                    // Green text for amount
                    $sheet->getStyle("D{$currentRow}")->applyFromArray([
                        'font' => [
                            'color' => [
                                'rgb' => '059669', // green
                            ],
                            'bold' => true,
                        ],
                    ]);
                }
                $currentRow++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Total Row
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("A{$this->lastRow}:G{$this->lastRow}")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E2E8F0',
                    ],
                ],
            ]);

        return [];
    }

    public function title(): string
    {
        return 'كشف حساب مساهم';
    }
}