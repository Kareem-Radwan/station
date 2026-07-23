@extends('layouts.app')
@section('title', 'تقرير الأرباح السنوية')
@section('content')

@include('partials.page-header', ['title' => 'تقرير الأرباح السنوية', 'icon' => 'fa-chart-pie'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end max-w-xl">
        <div>
            <label class="block text-slate-400 text-xs mb-1">السنة المالية</label>
            <select name="year" class="input-field w-full px-3 py-2 text-sm" required>
                @for($y=now()->year;$y>=now()->year-5;$y--)
                <option value="{{ $y }}" {{ request('year', now()->year)==$y?'selected':'' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fas fa-filter"></i> عرض التقرير</button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fas fa-file-excel"></i> إكسل</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي الإيرادات السنوية</p>
        <p class="text-3xl font-bold text-green-400">{{ number_format($annualRevenue, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي المصروفات السنوية</p>
        <p class="text-3xl font-bold text-red-400">{{ number_format($annualExpense, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border {{ $annualProfit >= 0 ? 'border-green-500/30' : 'border-red-500/30' }}">
        <p class="text-slate-400 text-xs mb-1">صافي الربح السنوي</p>
        <p class="text-3xl font-bold {{ $annualProfit >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ number_format($annualProfit, 0) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700"><h3 class="text-white font-bold">الملخص الشهري</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['الشهر','الإيرادات','المصروفات','صافي الربح'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($monthlyData as $m)
                <tr class="table-row">
                    <td class="px-4 py-3 text-white font-medium">{{ \Carbon\Carbon::create()->month($m['month'])->translatedFormat('F') }}</td>
                    <td class="px-4 py-3 text-green-400">{{ number_format($m['revenue'],0) }}</td>
                    <td class="px-4 py-3 text-red-400">{{ number_format($m['expense'],0) }}</td>
                    <td class="px-4 py-3 font-bold {{ $m['profit'] >= 0 ? 'text-amber-400' : 'text-red-400' }}">{{ number_format($m['profit'],0) }}</td>
                </tr>
                @endforeach
                <tr class="bg-slate-800/80">
                    <td class="px-4 py-4 text-white font-bold">الإجمالي السنوي</td>
                    <td class="px-4 py-4 text-green-400 font-bold text-lg">{{ number_format($annualRevenue,0) }}</td>
                    <td class="px-4 py-4 text-red-400 font-bold text-lg">{{ number_format($annualExpense,0) }}</td>
                    <td class="px-4 py-4 text-amber-400 font-bold text-xl">{{ number_format($annualProfit,0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

