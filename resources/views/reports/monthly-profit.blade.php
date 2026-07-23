@extends('layouts.app')
@section('title', 'تقرير الأرباح الشهرية')
@section('content')

@include('partials.page-header', ['title' => 'تقرير الأرباح الشهرية', 'icon' => 'fa-chart-line'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">الشهر</label>
            <select name="month" class="input-field w-full px-3 py-2 text-sm" required>
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ request('month', now()->month)==$m?'selected':'' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">السنة</label>
            <select name="year" class="input-field w-full px-3 py-2 text-sm" required>
                @for($y=now()->year;$y>=now()->year-3;$y--)
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

<div class="mb-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg text-blue-300 text-sm">
    <i class="fas fa-info-circle"></i> <strong>ملاحظة:</strong> هذا التقرير يعتمد على بيانات الخزينة (حركات الوارد والصادر الفعلية) لضمان التطابق التام مع تقرير الخزينة.
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    {{-- Revenues --}}
    <div class="card p-6 border-t-4 border-green-500">
        <h3 class="text-white font-bold mb-4 flex items-center gap-2"><i class="fas fa-arrow-down text-green-400"></i> الإيرادات والمبيعات</h3>
        <div class="space-y-3">
            <div class="flex justify-between"><span class="text-slate-400">مبيعات (مقبوضات من عملاء)</span><span class="text-green-400 font-bold">{{ number_format($revenues['orders'],0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">إيرادات أخرى (جميع المقبوضات الأخرى)</span><span class="text-green-400 font-bold">{{ number_format($revenues['other'],0) }}</span></div>
            <div class="flex justify-between border-t border-slate-700 pt-2"><span class="text-white font-bold">إجمالي الإيرادات</span><span class="text-green-400 font-bold text-xl">{{ number_format($totalRevenue,0) }}</span></div>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="card p-6 border-t-4 border-red-500">
        <h3 class="text-white font-bold mb-4 flex items-center gap-2"><i class="fas fa-arrow-up text-red-400"></i> التكاليف والمصروفات</h3>
        <div class="space-y-3">
            <div class="flex justify-between"><span class="text-slate-400">مشتريات المواد</span><span class="text-red-400">{{ number_format($expensesData['purchases'],0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">الرواتب والأجور (المدفوعة فعلياً)</span><span class="text-red-400">{{ number_format($expensesData['payroll'],0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">إيجارات المعدات والأرض</span><span class="text-red-400">{{ number_format($expensesData['rentals'],0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">المصروفات العامة (وقود، صيانة، سندات صرف...)</span><span class="text-red-400">{{ number_format($expensesData['general'],0) }}</span></div>
            <div class="flex justify-between border-t border-slate-700 pt-2"><span class="text-white font-bold">إجمالي المصروفات</span><span class="text-red-400 font-bold text-xl">{{ number_format($totalExpense,0) }}</span></div>
        </div>
    </div>
</div>

<div class="card p-8 text-center bg-gradient-to-br {{ $netProfit >= 0 ? 'from-slate-800 to-green-900/30' : 'from-slate-800 to-red-900/30' }}">
    <p class="text-slate-400 text-sm mb-2">صافي الربح / الخسارة للشهر المحدد</p>
    <p class="text-5xl font-bold {{ $netProfit >= 0 ? 'text-green-400' : 'text-red-400' }}">
        {{ number_format($netProfit, 0) }} <span class="text-lg font-normal text-slate-400">جنية</span>
    </p>
    <p class="text-slate-500 text-xs mt-3">
        <i class="fas fa-database"></i> المصدر: حركات الخزينة الفعلية (Treasury Transactions)
    </p>
</div>
@endsection

