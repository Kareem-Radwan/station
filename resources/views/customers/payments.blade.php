@extends('layouts.app')
@section('title', 'دفعات العميل: ' . $customer->name)
@section('content')

@include('partials.page-header', ['title' => 'دفعات العميل: ' . $customer->name, 'icon' => 'fa-money-bill-wave'])

<div class="flex gap-4 mb-6">
    <a href="{{ route('customer-payments.create', ['customer_id' => $customer->id]) }}" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm"><i class="fas fa-plus"></i> تسجيل دفعة جديدة</a>
    <a href="{{ route('customers.show', $customer) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-user"></i> ملف العميل</a>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','التاريخ','المبلغ','طريقة الدفع','رقم الطلب','ملاحظات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($payments ?? [] as $p)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $p->id }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $p->payment_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-green-400 font-bold">{{ number_format($p->amount,0) }}</td>
                    <td class="px-4 py-3"><span class="badge badge-blue">{{ ['cash'=>'نقدي','bank_transfer'=>'تحويل','check'=>'شيك'][$p->payment_method]??$p->payment_method }}</span></td>
                    <td class="px-4 py-3">@if($p->order_id) <a href="{{ route('orders.show',$p->order_id) }}" class="text-blue-400">#{{ $p->order_id }}</a> @else - @endif</td>
                    <td class="px-4 py-3 text-slate-400">{{ $p->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">لا توجد دفعات مسجلة لهذا العميل</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($payments) && method_exists($payments, 'hasPages') && $payments->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $payments->links() }}</div>@endif
</div>
@endsection
