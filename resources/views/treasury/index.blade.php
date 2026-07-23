@extends('layouts.app')
@section('title', 'الخزينة')
@section('content')

@include('partials.page-header', [
    'title' => 'الخزينة',
    'icon'  => 'fa-vault',
    'createRoute' => 'treasury.create',
    'createLabel' => 'قيد يدوي',
])

{{-- Balance Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
        <p class="text-slate-400 text-xs mb-1">الرصيد الحالي</p>
        <p class="text-3xl font-bold {{ ($currentBalance ?? 0) >= 0 ? 'text-amber-400' : 'text-red-400' }}">
            {{ number_format($currentBalance ?? 0, 2) }}
        </p>
        <p class="text-slate-500 text-sm">جنية</p>
    </div>

    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-green-400 to-green-600"></div>
        <p class="text-slate-400 text-xs mb-1">وارد هذا الشهر</p>
        <p class="text-3xl font-bold text-green-400">{{ number_format($monthlyIncoming ?? 0, 2) }}</p>
        <p class="text-slate-500 text-sm">جنية</p>
    </div>
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-400 to-red-600"></div>
        <p class="text-slate-400 text-xs mb-1">صادر هذا الشهر</p>
        <p class="text-3xl font-bold text-red-400">{{ number_format($monthlyOutgoing ?? 0, 2) }}</p>
        <p class="text-slate-500 text-sm">جنية</p>
    </div>
</div>

{{-- Filters --}}
<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">النوع</label>
            <select name="type" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="in"  {{ request('type')=='in'?'selected':'' }}>وارد</option>
                <option value="out" {{ request('type')=='out'?'selected':'' }}>صادر</option>
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">من</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">إلى</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i> بحث</button>
        <a href="{{ route('treasury.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['التاريخ','النوع','الفئة','الوصف','المبلغ','الرصيد بعد'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($transactions as $tx)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $tx->type==='in' ? 'badge-green':'badge-red' }}">{{ $tx->type_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-400">{{ $tx->category_label }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $tx->description ?? '-' }}</td>
                    <td class="px-4 py-3 font-bold {{ $tx->type==='in'?'text-green-400':'text-red-400' }}">
                        {{ $tx->type==='in' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-amber-400 font-medium">{{ number_format($tx->balance_after, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">لا توجد حركات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
