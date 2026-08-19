<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Carbon\Carbon;


use App\Models\SupplierPurchase;
use App\Models\SupplierPurchaseItem;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\Credit;

use App\Services\InventoryService;
use App\Services\TreasuryService;


class SupplierPurchaseSeeder extends Seeder
{

    public function run(
        InventoryService $inventoryService,
        TreasuryService $treasuryService
    ) {
        $filePath = storage_path('invo.xlsx');

        $rows = Excel::toArray(
            new class implements \Maatwebsite\Excel\Concerns\WithCalculatedFormulas {},
            $filePath
        )[2];

        // headers
        $header1 = $rows[1];
        $header2 = $rows[2];

        $header = [];

        foreach ($header1 as $i => $col) {

            $name = trim(
                ($col ?? '') . ' ' . ($header2[$i] ?? '')
            );

            $name = preg_replace('/\s+/', ' ', $name);

            $header[$i] = trim($name);
        }


        // remove title/header rows
        $rows = array_slice($rows, 3);



        $supplierMap = [

            'جولدن يونيتد بلال ابو الدهب' => 3,
            'سلام' => 2,
            'عمر ساري' => 1,
            'محمد فتحي' => 5,
            'هاي كيم' => 4,
        ];



        $inventoryMap = [

            'اسمنت' => 1,

            'اسمنتعاده' => 1,

            'رمل' => 2,

            'سن1' => 3,

            'سن2' => 4,

            'ماده' => 5,

            'مياه' => 6,

        ];


        $purchases = [];



        foreach ($rows as $row) {


            // ignore empty rows
            if (!array_filter($row)) {
                continue;
            }


            $row = array_pad($row, count($header), null);


            $row = array_combine($header, $row);


            $invoice = trim(
                $row['رقم الإذن'] ?? ''
            );

            // allow empty permission number
            if (!$invoice) {
                $invoice =
                    'AUTO-' .
                    $supplierName . '-' .
                    $row['التاريخ'] . '-' .
                    uniqid();
            }

            $supplierName = $this->cleanText(
                $row['إسم المورد'] ?? ''
            );


            if (!isset($supplierMap[$supplierName])) {
                continue;
            }


            $qty = $this->number(
                $row['الوارد صافى كمية'] ?? 0
            );


            $amount = $this->number(
                $row['قيمة'] ?? 0
            );


            if (!isset($purchases[$invoice])) {


                $purchases[$invoice] = [

                    'supplier_id' =>
                        $supplierMap[$supplierName],


                    'invoice_number' =>
                        $invoice,


                    'purchase_date' =>
                        $this->convertDate(
                            $row['التاريخ'] ?? null
                        ),


                    'notes'=>null,


                    'items'=>[],


                ];

            }

            $itemName = $this->cleanText(
                $row['إسم الصنف'] ?? ''
            );


            $itemKey = str_replace(
                ' ',
                '',
                $itemName
            );


            $inventoryId =
                $inventoryMap[$itemKey]
                ??
                null;


            $carNumber = trim(
                $row['رقم السيارة'] ?? ''
            );


            $description = trim($row['إسم الصنف'] ?? '');

            if ($carNumber) {

                $description .= ' - سيارة ' . $carNumber;

            }



            $purchases[$invoice]['items'][] = [

                'inventory_item_id'=>$inventoryId,

                'description'=>$description,

                'quantity'=>$qty,

                'unit'=>
                    $row['الوحدة'] ?? 'طن',

                'unit_price'=>
                    $qty > 0
                    ? $amount / $qty
                    : 0,

                'total_price'=>$amount,

            ];

        }

        /*
         |--------------------------------------------------------------------------
         | Insert using same controller logic
         |--------------------------------------------------------------------------
         */

        DB::transaction(function() use(
            $purchases,
            $inventoryService,
            $treasuryService
        ){


            foreach($purchases as $data){


                $items=$data['items'];



                $totalAmount =
                    collect($items)
                    ->sum('total_price');



                $cashAmount=0;

                $creditAmount=$totalAmount;



                $purchase =
                SupplierPurchase::create([


                    'supplier_id'=>
                        $data['supplier_id'],


                    'invoice_number'=>
                        $data['invoice_number'],


                    'purchase_date'=>
                        $data['purchase_date'],


                    'total_amount'=>
                        $totalAmount,


                    'payment_type'=>'credit',


                    'cash_amount'=>0,


                    'credit_amount'=>
                        $creditAmount,


                    'due_date'=>
                        now()->addDays(30)
                            ->format('Y-m-d'),


                    'status'=>'pending',


                    'notes'=>
                        $data['notes'],


                    'created_by'=>1,


                ]);




                foreach($items as $item){



                    SupplierPurchaseItem::create([


                        'supplier_purchase_id'=>
                            $purchase->id,


                        'inventory_item_id'=>
                            $item['inventory_item_id'],


                        'description'=>
                            $item['description'],


                        'quantity'=>
                            $item['quantity'],


                        'unit'=>
                            $item['unit'],


                        'unit_price'=>
                            $item['unit_price'],


                        'total_price'=>
                            $item['total_price'],


                    ]);



                    /*
                     * Same as controller:
                     * stock in inventory
                     */

                    if($item['inventory_item_id']){


                        $inventoryService->stockIn(

                            (int)$item['inventory_item_id'],


                            (float)$item['quantity'],


                            [

                                'supplier_id'=>
                                    $data['supplier_id'],


                                'unit_cost'=>
                                    $item['unit_price'],


                                'reference_type'=>
                                    'purchase',


                                'reference_id'=>
                                    $purchase->id,


                                'date'=>
                                    $data['purchase_date'],


                            ]

                        );


                    }


                }





                /*
                 * Supplier balance
                 */

                Supplier::find($data['supplier_id'])
                    ->increment(
                        'balance',
                        $creditAmount
                    );





                /*
                 * Credit record
                 */

                Credit::create([


                    'creditable_type'=>'supplier',


                    'creditable_id'=>
                        $data['supplier_id'],


                    'reference_type'=>'purchase',


                    'reference_id'=>
                        $purchase->id,


                    'amount'=>
                        $creditAmount,

                    'due_date'=>
                        now()->addDays(30)->format('Y-m-d'),

                    'status'=>'pending',


                    'created_by'=>1,


                ]);



            }


        });


    }


    private function number($value)
    {
        if (!$value) {
            return 0;
        }

        return (float) str_replace(
            ',',
            '',
            $value
        );
    }

    private function cleanText($value)
    {
        return preg_replace(
            '/\s+/',
            ' ',
            trim((string)$value)
        );
    }

    private function convertDate($date)
    {

        if(!$date){
            return now()->format('Y-m-d');
        }


        $date=trim($date);



        if(is_numeric($date) && strlen($date)<=6){

            return Carbon::create(1900,1,1)
                ->addDays($date-2)
                ->format('Y-m-d');

        }



        $date=preg_replace('/(\d{4})\d+$/','$1',$date);



        foreach([
            'd/m/Y',
            'd-m-Y',
            'Y-m-d'

        ] as $format){


            try {

                return Carbon::createFromFormat(
                    $format,
                    $date
                )->format('Y-m-d');


            } catch(\Exception $e){}


        }



        return now()->format('Y-m-d');

    }

}