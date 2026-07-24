@extends('layouts.app')
@section('title', 'حركات المخزون - '.$inventory->name_ar)
@section('content')

@include('partials.page-header', ['title' => 'حركات: '.$inventory->name_ar, 'icon' => 'fa-history'])

<div class="flex gap-4 mb-6">
    <a href="{{ route('inventory.stock-in', $inventory) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
        <i class="fas fa-arrow-down text-green-400"></i> وارد
    </a>
    <a href="{{ route('inventory.stock-out', $inventory) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
        <i class="fas fa-arrow-up text-red-400"></i> صادر
    </a>
    <div class="stat-card rounded-xl px-5 py-3 flex items-center gap-3">
        <span class="text-slate-400 text-sm">الرصيد الحالي:</span>
        <span class="text-amber-400 font-bold text-xl">{{ number_format($inventory->current_stock, 1) }} {{ $inventory->unit }}</span>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['التاريخ','رقم الفاتورة','النوع','الكمية','الرصيد بعد','سعر الوحدة','المورد','ملاحظات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($movements as $m)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $m->movement_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-300 font-mono text-xs whitespace-nowrap">
                        @if($m->reference_type === 'purchase' && $m->purchase)
                            <a href="{{ route('supplier-purchases.show', $m->purchase) }}" class="text-blue-400 hover:text-blue-300 inline-flex items-center gap-1">
                                <i class="fas fa-file-invoice text-slate-500 text-xs"></i>
                                {{ $m->purchase->invoice_number ?? '#' . $m->purchase->id }}
                            </a>
                        @elseif($m->invoice_number)
                            <span class="text-slate-300">{{ $m->invoice_number }}</span>
                        @else
                            <span class="text-slate-600">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $m->type==='in'?'badge-green':'badge-red' }}">
                            <i class="fas {{ $m->type==='in'?'fa-arrow-down':'fa-arrow-up' }} ml-1"></i>
                            {{ $m->type==='in'?'وارد':'صادر' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold {{ $m->type==='in'?'text-green-400':'text-red-400' }}">
                        {{ $m->type==='in'?'+':'-' }}{{ number_format($m->quantity, 3) }} {{ $inventory->unit }}
                    </td>
                    <td class="px-4 py-3 text-amber-400 font-medium">{{ number_format($m->balance_after, 1) }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $m->unit_cost ? number_format($m->unit_cost,2) : '-' }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $m->supplier?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $m->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">
                    <i class="fas fa-history text-4xl mb-3 opacity-30"></i><br>لا توجد حركات مسجلة
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $movements->links() }}</div>@endif
</div>
@endsection
