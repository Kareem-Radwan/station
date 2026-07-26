@extends('layouts.app')
@section('title', 'دفعات الموردين')
@section('content')

@include('partials.page-header', [
    'title'       => 'دفعات الموردين',
    'icon'        => 'fa-hand-holding-usd',
    'createRoute' => 'supplier-payments.create',
    'createLabel' => 'تسجيل دفعة',
])

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','المورد','التاريخ','النوع','المبلغ','طريقة الدفع','الفاتورة المرتبطة','ملاحظات','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($payments as $p)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $p->id }}</td>
                    <td class="px-4 py-3"><a href="{{ route('suppliers.show',$p->supplier) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $p->supplier->name }}</a></td>
                    <td class="px-4 py-3 text-slate-300">{{ $p->payment_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        @if(($p->payment_type ?? 'payment') === 'deduction')
                            <span class="badge badge-purple">خصم</span>
                        @else
                            <span class="badge badge-green">دفعة</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-green-400 font-bold">{{ number_format($p->amount,0) }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-blue">{{ ['cash'=>'نقدي','bank_transfer'=>'تحويل','check'=>'شيك'][$p->payment_method]??$p->payment_method }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-400 text-xs">
                        @if($p->purchase)<a href="{{ route('supplier-purchases.show',$p->purchase) }}" class="text-blue-400 hover:text-blue-300">#{{ $p->supplier_purchase_id }}</a>@else -@endif
                    </td>
                    <td class="px-4 py-3 text-slate-400">{{ $p->notes ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('supplier-payments.edit',$p) }}" class="text-blue-400 hover:text-blue-300" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('supplier-payments.destroy',$p) }}" method="POST" class="inline" onsubmit="return confirm('حذف {{ ($p->payment_type ?? 'payment') === 'deduction' ? 'الخصم' : 'الدفعة' }}؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-hand-holding-usd text-4xl mb-3 opacity-30"></i><br>لا توجد دفعات أو خصومات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $payments->links() }}</div>@endif
</div>
@endsection
