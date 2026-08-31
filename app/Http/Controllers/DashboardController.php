<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\InventoryItem;
use App\Models\TreasuryTransaction;
use App\Models\Order;
use App\Models\Customer;
use App\Models\WeeklySchedule;
use App\Models\Expense;

class DashboardController extends Controller
{
    public function index()
    {
        Credit::checkAndMarkOverdue();

        $treasuryBalance = (float) TreasuryTransaction::where('type', 'in')->sum('amount') - (float) TreasuryTransaction::where('type', 'out')->sum('amount');
        $pendingOrders      = Order::where('status', 'pending')->count();
        $scheduledOrders    = Order::where('status', 'scheduled')->count();
        $overdueCredits     = Credit::where('status', 'overdue')->count();
        $pendingCredits     = Credit::where('status', 'pending')->count();
        
        // Credits due within 2 days (warning alert) - paginated
        $creditsDueSoon     = Credit::with('creditable')
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(2)->toDateString()])
            ->orderBy('due_date')
            ->paginate(3, ['*'], 'credits_page');
        
        $lowStockItems      = InventoryItem::whereRaw('current_stock <= alert_threshold')->get();
        $upcomingSchedule   = WeeklySchedule::with('entries.customer')
            ->where('week_start', '>=', now()->toDateString())
            ->orderBy('week_start')
            ->first();
        $todayOrders        = Order::with('customer')->whereDate('delivery_date', today())->get();

        // Monthly revenue/expenses for chart (last 6 months)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->startOfMonth()->subMonths($i);
            $start = $date->copy()->startOfMonth()->toDateString();
            $end   = $date->copy()->endOfMonth()->toDateString();
            $chartData[] = [
                'label'    => $this->monthName($date->month),
                'revenue'  => (float)TreasuryTransaction::where('type','in')->whereBetween('transaction_date',[$start,$end])->sum('amount'),
                'expenses' => (float)TreasuryTransaction::where('type','out')->whereBetween('transaction_date',[$start,$end])->sum('amount'),
            ];
        }

        $expenseBreakdown = Expense::where('expense_date', '>=', now()->startOfMonth()->toDateString())
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        return view('dashboard.index', compact(
            'treasuryBalance','pendingOrders','scheduledOrders',
            'overdueCredits','pendingCredits','creditsDueSoon','lowStockItems',
            'upcomingSchedule','todayOrders','chartData','expenseBreakdown'
        ));
    }

    private function monthName(int $m): string
    {
        $months = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                   7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
        return $months[$m] ?? '';
    }
}
