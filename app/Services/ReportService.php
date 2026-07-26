<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\TreasuryTransaction;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Credit;
use App\Models\Payroll;
use App\Models\Equipment;
use App\Models\EquipmentFuelLog;
use App\Models\EquipmentMaintenance;
use Carbon\Carbon;

class ReportService
{
    public function monthlyProfitReport(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Use TreasuryTransaction as single source of truth
        $revenue  = TreasuryTransaction::where('type', 'in')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $expenses = TreasuryTransaction::where('type', 'out')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        // Breakdown by category
        $breakdown = TreasuryTransaction::where('type', 'out')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn($item) => [$item->category => (float)$item->total]);

        return [
            'period'    => $this->monthName($month) . ' ' . $year,
            'month'     => $month,
            'year'      => $year,
            'revenue'   => (float)$revenue,
            'expenses'  => (float)$expenses,
            'profit'    => (float)$revenue - (float)$expenses,
            'breakdown' => $breakdown,
        ];
    }

    public function annualProfitReport(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = $this->monthlyProfitReport($m, $year);
        }

        return [
            'year'           => $year,
            'months'         => $months,
            'total_revenue'  => collect($months)->sum('revenue'),
            'total_expenses' => collect($months)->sum('expenses'),
            'total_profit'   => collect($months)->sum('profit'),
        ];
    }

    public function customerBalanceReport(?string $fromDate, ?string $toDate): \Illuminate\Support\Collection
    {
        return Customer::with(['orders' => function($q) {
                $q->where('status', '!=', 'cancelled');
            }, 'payments', 'credits'])
            ->when($fromDate && $toDate, function ($q) use ($fromDate, $toDate) {
                $q->whereHas('orders', fn($oq) => $oq->where('status', '!=', 'cancelled')
                    ->whereBetween('delivery_date', [$fromDate, $toDate]));
            })
            ->get()
            ->map(function($c) {
                $incomingPayments = (float)$c->payments->where('amount', '>', 0)->sum('amount')
                                  + (float)TreasuryTransaction::where('reference_type', 'customer')->where('reference_id', $c->id)->where('type', 'in')->sum('amount');
                $outgoingPayments = abs((float)$c->payments->where('amount', '<', 0)->sum('amount'))
                                  + (float)TreasuryTransaction::where('reference_type', 'customer')->where('reference_id', $c->id)->where('type', 'out')->sum('amount');
                $totalOrders = (float)$c->orders->sum('total_amount');
                $totalCash   = (float)$c->orders->sum('cash_amount');
                $outstanding = ($totalOrders - $totalCash) + $outgoingPayments - $incomingPayments;

                return [
                    'customer'          => $c,
                    'order_count'       => $c->orders->count(),
                    'total_orders'      => $totalOrders,
                    'total_payments'    => $incomingPayments + $totalCash,
                    'total_cash'        => $totalCash,
                    'outstanding'       => $outstanding,
                    'cement_balance'    => (float)$c->cement_balance,
                    'total_concrete_m3' => (float)$c->orders->sum('quantity_m3'),
                ];
            });
    }

    public function supplierBalanceReport(?string $fromDate, ?string $toDate): \Illuminate\Support\Collection
    {
        return Supplier::with(['purchases', 'payments'])
            ->get()
            ->map(fn($s) => [
                'supplier'       => $s,
                'total_purchases'=> (float)$s->purchases->sum('total_amount'),
                'total_payments' => (float)$s->payments->sum('amount'),
                'outstanding'    => (float)$s->balance,
            ]);
    }

    public function inventoryStatusReport(): \Illuminate\Support\Collection
    {
        return InventoryItem::all()->map(fn($item) => [
            'item'         => $item,
            'current_stock'=> (float)$item->current_stock,
            'threshold'    => (float)$item->alert_threshold,
            'is_low'       => $item->isBelowAlert(),
        ]);
    }

    public function dueCreditReport(): \Illuminate\Support\Collection
    {
        Credit::checkAndMarkOverdue();

        return Credit::where('status', '!=', 'paid')
            ->with(['createdBy', 'creditable'])
            ->orderBy('due_date')
            ->get()
            ->map(function ($credit) {
                return [
                    'credit'      => $credit,
                    'party'       => $credit->creditable,
                    'party_type'  => $credit->creditable_type === 'customer' ? 'عميل' : 'مورد',
                    'days_left'   => now()->diffInDays($credit->due_date, false),
                ];
            });
    }

    public function equipmentCostReport(?string $fromDate, ?string $toDate): \Illuminate\Support\Collection
    {
        return Equipment::with(['fuelLogs', 'maintenance'])->get()->map(function ($eq) use ($fromDate, $toDate) {
            $fuelQuery = $eq->fuelLogs();
            $maintQuery = $eq->maintenance();

            if ($fromDate && $toDate) {
                $fuelQuery  = $fuelQuery->whereBetween('log_date', [$fromDate, $toDate]);
                $maintQuery = $maintQuery->whereBetween('maintenance_date', [$fromDate, $toDate]);
            }

            $fuelCost  = (float)$fuelQuery->sum('total_cost');
            $maintCost = (float)$maintQuery->sum('cost');

            return [
                'equipment'        => $eq,
                'fuel_cost'        => $fuelCost,
                'maintenance_cost' => $maintCost,
                'total_cost'       => $fuelCost + $maintCost,
            ];
        });
    }

    public function payrollReport(?int $month = null, ?int $year = null, ?int $employeeId = null): \Illuminate\Database\Eloquent\Collection
    {
        return Payroll::with(['employee', 'borrowDeductions.borrow'])
            ->when($month, fn($q, $v) => $q->where('period_month', $v))
            ->when($year, fn($q, $v) => $q->where('period_year', $v))
            ->when($employeeId, fn($q, $v) => $q->where('employee_id', $v))
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();
    }

    private function monthName(int $m): string
    {
        $months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                   7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
        return $months[$m] ?? '';
    }
}
