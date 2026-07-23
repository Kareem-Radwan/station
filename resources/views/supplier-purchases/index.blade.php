@extends('layouts.app')
@section('title', 'فواتير المشتريات')
@section('content')

@include('partials.page-header', [
    'title'       => 'فواتير المشتريات',
    'icon'        => 'fa-shopping-cart',
    'createRoute' => 'supplier-purchases.create',
    'createLabel' => 'فاتورة جديدة',
])

<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-40 flex-1">
            <label class="text-slate-400 text-xs mb-1 block">المورد</label>
            <select name="supplier_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">كل الموردين</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-32">
            <label class="text-slate-400 text-xs mb-1 block">الحالة</label>
            <select name="status" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach(['pending'=>'معلق','partial'=>'جزئي','paid'=>'مسدد'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i> بحث</button>
        <a href="{{ route('supplier-purchases.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','المورد','التاريخ','رقم الفاتورة','الإجمالي','نقدي','آجل','الحالة',''] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($purchases as $p)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $p->id }}</td>
                    <td class="px-4 py-3"><a href="{{ route('suppliers.show',$p->supplier) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $p->supplier->name }}</a></td>
                    <td class="px-4 py-3 text-slate-300">{{ $p->purchase_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $p->invoice_number ?? '-' }}</td>
                    <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($p->total_amount,0) }}</td>
                    <td class="px-4 py-3 text-green-400">{{ $p->cash_amount ? number_format($p->cash_amount,0) : '-' }}</td>
                    <td class="px-4 py-3 text-red-400">{{ $p->credit_amount ? number_format($p->credit_amount,0) : '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $p->status==='paid'?'badge-green':($p->status==='partial'?'badge-yellow':'badge-red') }}">
                            {{ ['pending'=>'معلق','partial'=>'جزئي','paid'=>'مسدد'][$p->status]??$p->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('supplier-purchases.show',$p) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-shopping-cart text-4xl mb-3 opacity-30"></i><br>لا توجد فواتير</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $purchases->links() }}</div>@endif
</div>
@endsection
