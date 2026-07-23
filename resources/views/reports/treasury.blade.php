@extends('layouts.app')
@section('title', 'تقرير حركة الخزينة')
@section('content')

@include('partials.page-header', ['title' => 'تقرير حركة الخزينة والمالية', 'icon' => 'fa-vault'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">النوع</label>
            <select name="type" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="in" {{ request('type')=='in'?'selected':'' }}>وارد (+)</option>
                <option value="out" {{ request('type')=='out'?'selected':'' }}>صادر (-)</option>
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
            <input type="date" name="from_date" value="{{ request('from_date', today()->startOfMonth()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
            <input type="date" name="to_date" value="{{ request('to_date', today()->endOfMonth()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fas fa-filter"></i> عرض</button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fas fa-file-excel"></i> إكسل</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="stat-card rounded-2xl p-5 border border-amber-500/30 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-amber-500"></div>
        <p class="text-slate-400 text-xs mb-1">الرصيد الفعلي الحالي للخزينة</p>
        <p class="text-2xl font-bold text-amber-400">{{ number_format($currentBalance, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-green-500"></div>
        <p class="text-slate-400 text-xs mb-1">إجمالي الوارد (خلال الفترة)</p>
        <p class="text-2xl font-bold text-green-400">{{ number_format($totalIn, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
        <p class="text-slate-400 text-xs mb-1">إجمالي الصادر (خلال الفترة)</p>
        <p class="text-2xl font-bold text-red-400">{{ number_format($totalOut, 0) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['التاريخ','الفئة','البيان','وارد','صادر','الرصيد التراكمي'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($transactions as $t)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ \Carbon\Carbon::parse($t->transaction_date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3"><span class="badge badge-blue">{{ $t->category_label }}</span></td>
                    <td class="px-4 py-3 text-white">{{ $t->description }}</td>
                    <td class="px-4 py-3 font-bold text-green-400">{{ $t->type==='in' ? number_format($t->amount,0) : '-' }}</td>
                    <td class="px-4 py-3 font-bold text-red-400">{{ $t->type==='out' ? number_format($t->amount,0) : '-' }}</td>
                    <td class="px-4 py-3 font-bold text-amber-400">{{ number_format($t->balance_after,0) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">لا توجد حركات في هذه الفترة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection

