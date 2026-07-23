<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SupplierBalanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private $rowNumber = 1;

    private ?string $fromDate = null;
    private ?string $toDate = null;

    public function __construct(
        private ?int $supplierId = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ) {
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
    }


    public function collection()
    {
        if ($this->supplierId) {

            $supplier = \App\Models\Supplier::findOrFail($this->supplierId);

            $fromDate = $this->fromDate ?? now()->startOfMonth()->toDateString();
            $toDate   = $this->toDate ?? now()->toDateString();


            /*
             |--------------------------------------------------------------------------
             | Get same data as controller
             |--------------------------------------------------------------------------
             */

            $purchases = $supplier->purchases()
                ->whereBetween('purchase_date', [$fromDate, $toDate])
                ->orderBy('purchase_date')
                ->get();


            $payments = $supplier->payments()
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->orderBy('payment_date')
                ->get();


            $deductions = $supplier->payments()
                ->where('payment_type','deduction')
                ->whereBetween('payment_date', [$fromDate,$toDate])
                ->orderBy('payment_date')
                ->get();


            $paidCredits = \App\Models\Credit::where('creditable_type','supplier')
                ->where('creditable_id',$supplier->id)
                ->where('status','paid')
                ->whereBetween('paid_date',[$fromDate,$toDate])
                ->orderBy('paid_date')
                ->get();


            $rentalShifts = \App\Models\RentalShift::whereHas('contract', function($q) use($supplier){

                $q->where('supplier_id',$supplier->id);

            })
            ->whereBetween('shift_date',[$fromDate,$toDate])
            ->with('contract')
            ->orderBy('shift_date')
            ->get();



            $stockInMovements = \App\Models\InventoryMovement::where('supplier_id',$supplier->id)
                ->where('type','in')
                ->whereBetween('movement_date',[$fromDate,$toDate])
                ->with('item')
                ->orderBy('movement_date')
                ->get();



            /*
             |--------------------------------------------------------------------------
             | Totals
             |--------------------------------------------------------------------------
             */

            $totalPurchases = $purchases->sum('total_amount');

            $totalPaymentsOnly = $payments
                ->where('payment_type','!=','deduction')
                ->sum('amount');

            $totalDeductions = $deductions->sum('amount');

            $totalRentalShifts = $rentalShifts->sum('total_cost');

            $totalCashPurchases = $purchases->sum('cash_amount');

            $totalCreditPayments = $paidCredits->sum('amount');

            $totalStockIn = $stockInMovements->sum('total_cost');


            $totalPayments =
                $totalPaymentsOnly +
                $totalCashPurchases +
                $totalCreditPayments;

            $hasCars = $totalRentalShifts > 0;

            if($hasCars){

                $balance =
                    $totalRentalShifts -
                    ($totalDeductions + $totalPayments);

            }else{

                $balance =
                    $totalPurchases
                    - $totalPayments
                    - $totalDeductions;

            }



            $balanceType =
                $balance > 0
                ? 'دائن (مطلوب له)'
                :
                ($balance < 0
                ? 'مدين (دفعنا زيادة)'
                :
                'متعادل');




            /*
             |--------------------------------------------------------------------------
             | Build transactions exactly like Blade
             |--------------------------------------------------------------------------
             */


            $transactions = collect();

            $runningBalance = 0;



            /*
             |--------------------------------------------------------------------------
             | Group stock details inside invoice
             |--------------------------------------------------------------------------
             */

            $stockGrouped = $stockInMovements->groupBy(function($row){

                return $row->purchase_id;

            });



            foreach($purchases as $purchase){


                $invoiceStock =
                    $stockGrouped->get($purchase->id,collect());


                $details = "";


                foreach($invoiceStock as $stock){

                    $details .=
                        " | "
                        .$stock->item->name_ar
                        ." "
                        .number_format($stock->quantity,2)
                        ." "
                        .$stock->item->unit;

                }



                $credit =
                    $purchase->total_amount -
                    $purchase->cash_amount;


                $runningBalance += $credit;



                $transactions->push([

                    'date'=>$purchase->purchase_date,

                    'description'=>
                        'مشتريات إذن '
                        .($purchase->invoice_number ?? $purchase->id)
                        .$details,

                    'debit'=>$purchase->cash_amount,

                    'credit'=>$purchase->total_amount,

                    'balance'=>$runningBalance,

                    'tx_type'=>'purchase'

                ]);

            }




            foreach($payments as $payment){


                if($payment->payment_type=='deduction'){
                    continue;
                }


                $runningBalance -= $payment->amount;


                $transactions->push([

                    'date'=>$payment->payment_date,

                    'description'=>
                        'دفعة للمورد - '
                        .$payment->payment_method,

                    'debit'=>$payment->amount,

                    'credit'=>0,

                    'balance'=>$runningBalance,

                    'tx_type'=>'payment'

                ]);

            }




            foreach($deductions as $deduction){


                $runningBalance += $deduction->amount;


                $transactions->push([

                    'date'=>$deduction->payment_date,

                    'description'=>
                        'خصم من المورد - '
                        .($deduction->notes ?? ''),

                    'debit'=>0,

                    'credit'=>$deduction->amount,

                    'balance'=>$runningBalance,

                    'tx_type'=>'deduction'

                ]);

            }





            foreach($paidCredits as $credit){


                $runningBalance -= $credit->amount;


                $transactions->push([

                    'date'=>$credit->paid_date,

                    'description'=>
                        'سداد آجل للمورد - '
                        .($credit->notes ?? 'دفعة آجلة'),

                    'debit'=>$credit->amount,

                    'credit'=>0,

                    'balance'=>$runningBalance,

                    'tx_type'=>'credit_payment'

                ]);

            }





            foreach($rentalShifts as $shift){


                $details =
                    'ساعات: '.$shift->hours;


                if($shift->gratuities > 0)
                    $details .=
                        ' | اكراميات '
                        .number_format($shift->gratuities,0);


                if($shift->cards_cost > 0)
                    $details .=
                        ' | كارتات '
                        .number_format($shift->cards_cost,0);


                if($shift->driver_allowance > 0)
                    $details .=
                        ' | معيشة '
                        .number_format($shift->driver_allowance,0);


                if($shift->fuel_cost > 0)
                    $details .=
                        ' | وقود '
                        .number_format($shift->fuel_cost,0);



                $transactions->push([

                    'date'=>$shift->shift_date,

                    'description'=>
                        'وردية سيارة: '
                        .$shift->contract->equipment_name
                        .' ('
                        .($shift->contract->car_number ?? '')
                        .') - '.$details,


                    'debit'=>0,

                    'credit'=>$shift->total_cost,

                    'balance'=>null,

                    'tx_type'=>'rental_shift'

                ]);

            }



            $transactions =
                $transactions
                ->sortBy('date')
                ->values();



            /*
             |--------------------------------------------------------------------------
             | Excel rows
             |--------------------------------------------------------------------------
             */

            $data = collect();


            $data->push([
                'كشف حساب مورد',
                '',
                '',
                '',
                '',
                ''
            ]);


            $data->push(['المورد',$supplier->name]);

            $data->push(['الهاتف',$supplier->phone ?? '-']);

            $data->push(['العنوان',$supplier->address ?? '-']);

            $data->push([
                'المواد',
                is_array($supplier->materials)
                ? implode('، ',$supplier->materials)
                : '-'
            ]);

            $data->push(['نوع الدفع',$supplier->payment_type_label]);

            $data->push(['من تاريخ',$fromDate]);

            $data->push(['إلى تاريخ',$toDate]);

            $data->push(['','']);



            if(!$hasCars){

                $data->push([
                    'إجمالي المشتريات',
                    number_format($totalPurchases,2)
                ]);

            }


            $data->push([
                'إجمالي المدفوعات',
                number_format($totalPayments,2)
            ]);


            $data->push([
                'إجمالي الخصومات',
                number_format($totalDeductions,2)
            ]);


            if($hasCars){

                $data->push([
                    'إجمالي ورديات السيارات',
                    number_format($totalRentalShifts,2)
                ]);

            }


            $data->push([
                'الرصيد للفترة',
                number_format(abs($balance),2)
                .' '.$balanceType
            ]);


            $data->push(['','']);



            $data->push([

                'التاريخ',
                'البيان',
                'نوع الحركة',
                'مدين (دفعنا له)',
                'دائن (اشترينا منه)',
                'الرصيد التراكمي'

            ]);



            foreach($transactions as $t){


                $labels=[

                    'purchase'=>'مشتريات',

                    'payment'=>'دفعة',

                    'credit_payment'=>'سداد آجل',

                    'deduction'=>'خصم',

                    'rental_shift'=>'وردية'

                ];


                $data->push([

                    \Carbon\Carbon::parse($t['date'])
                        ->format('Y-m-d'),

                    $t['description'],

                    $labels[$t['tx_type']] ?? '-',

                    $t['debit']>0
                        ?number_format($t['debit'],2)
                        :'-',

                    $t['credit']>0
                        ?number_format($t['credit'],2)
                        :'-',

                    $t['balance']!==null
                        ?number_format($t['balance'],2)
                        :'-'

                ]);

            }


            $this->rowNumber=$data->count();


            return $data;

        }



        // Summary export
        $fromDate =
            $this->fromDate ??
            now()->startOfMonth()->toDateString();


        $toDate =
            $this->toDate ??
            now()->toDateString();


        $data =
            (new ReportService())
            ->supplierBalanceReport($fromDate,$toDate);


        return collect($data)->map(function($r){

            return [

                $r['supplier']->name,

                $r['supplier']->phone ?? '-',

                $r['supplier']->payment_type_label ?? '-',

                number_format($r['total_purchases'],2),

                number_format($r['total_payments'],2),

                number_format(abs($r['outstanding']),2)

            ];

        });

    }




    public function headings(): array
    {
        return $this->supplierId
            ? []
            :
            [
                'اسم المورد',
                'الهاتف',
                'نوع الدفع',
                'إجمالي المشتريات',
                'المدفوعات',
                'الرصيد'
            ];
    }



    public function styles(Worksheet $sheet)
    {

        $sheet->setRightToLeft(true);


        foreach(range('A','F') as $col){

            $sheet->getColumnDimension($col)
                ->setAutoSize(true);

        }


        if($this->supplierId){


            $sheet->mergeCells('A1:F1');


            $sheet->getStyle('A1')
                ->getFont()
                ->setBold(true)
                ->setSize(16);



            $headerRow = $this->rowNumber - count($sheet->toArray()) + 1;


            foreach($sheet->getRowIterator() as $row){

                $cells=$row->getCellIterator();

                foreach($cells as $cell){

                    if($cell->getValue()=='التاريخ'){

                        $headerRow=$cell->getRow();

                    }

                }

            }



            $sheet->getStyle("A{$headerRow}:F{$headerRow}")
                ->applyFromArray([

                    'font'=>[
                        'bold'=>true,
                        'color'=>[
                            'rgb'=>'FFFFFF'
                        ]
                    ],

                    'fill'=>[
                        'fillType'=>Fill::FILL_SOLID,
                        'startColor'=>[
                            'rgb'=>'1e3a5f'
                        ]
                    ]

                ]);

        }


        return [];

    }



    public function title(): string
    {

        return $this->supplierId
            ? 'كشف حساب مورد تفصيلي'
            : 'أرصدة الموردين';

    }

}