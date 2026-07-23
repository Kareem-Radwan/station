@extends('layouts.app')
@section('title', 'تقرير الرواتب')
@section('content')

@include('partials.page-header', ['title' => 'تقرير الرواتب', 'icon' => 'fa-money-bill-wave'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">الشهر</label>
            <select name="month" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ request('month')==$m?'selected':'' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">السنة</label>
            <select name="year" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @for($y=now()->year;$y>=now()->year-3;$y--)
                <option value="{{ $y }}" {{ request('year')==$y?'selected':'' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">الموظف (اختياري)</label>
            <select name="employee_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach(\App\Models\Employee::all() as $emp)
                <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fas fa-filter"></i> عرض التقرير</button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fas fa-file-excel"></i> إكسل</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">أساسي</p>
        <p class="text-xl font-bold text-white">{{ number_format($totals['base'],0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إضافي</p>
        <p class="text-xl font-bold text-amber-400">{{ number_format($totals['overtime'],0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">خصومات</p>
        <p class="text-xl font-bold text-red-400">{{ number_format($totals['deductions'],0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-green-500/30">
        <p class="text-slate-400 text-xs mb-1">الصافي المنصرف</p>
        <p class="text-xl font-bold text-green-400">{{ number_format($totals['net'],0) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['الموظف','الشهر','أساسي','إضافي','خصومات','خصم سلفة','رصيد السلفة المتبقي','الصافي','الحالة'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($payrolls as $p)
                @php
                    $borrowDeducted = $p->borrowDeductions->sum('amount');
                    $activeBorrows = $p->employee->borrows()->where('status', 'active')->get();
                    $totalRemainingBorrow = $activeBorrows->sum('remaining_amount');
                @endphp
                <tr class="table-row">
                    <td class="px-4 py-3 text-white">{{ $p->employee->name }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $p->month }}/{{ $p->year }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ number_format($p->base_salary,0) }}</td>
                    <td class="px-4 py-3 text-amber-400">{{ number_format($p->overtime_pay,0) }}</td>
                    <td class="px-4 py-3 text-red-400">{{ number_format($p->total_deductions,0) }}</td>
                    <td class="px-4 py-3 text-orange-400">{{ number_format($borrowDeducted,0) }}</td>
                    <td class="px-4 py-3 {{ $totalRemainingBorrow > 0 ? 'text-yellow-400' : 'text-slate-500' }}">
                        {{ number_format($totalRemainingBorrow,0) }}
                    </td>
                    <td class="px-4 py-3 text-green-400 font-bold">{{ number_format($p->net_salary,0) }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $p->status==='paid'?'badge-green':'badge-yellow' }}">{{ $p->status==='paid'?'مسدد':'معلق' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-500">لا توجد رواتب مسجلة للفترة المحددة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $payrolls->links() }}</div>@endif
</div>
@endsection

