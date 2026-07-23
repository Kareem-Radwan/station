@extends('layouts.app')
@section('title', $supplier->name)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-white text-sm">الموردون</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">{{ $supplier->name }}</span>
        </div>
        <span class="badge {{ $supplier->is_active ? 'badge-green':'badge-gray' }}">{{ $supplier->is_active?'نشط':'موقف' }}</span>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('supplier-purchases.create') }}?supplier_id={{ $supplier->id }}" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm"><i class="fas fa-plus"></i> فاتورة مشتريات</a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card p-6 space-y-4">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2"><i class="fas fa-truck text-amber-400"></i> بيانات المورد</h3>
        <div class="space-y-3 text-sm">
            @foreach([['الهاتف',$supplier->phone??'-','fa-phone'],['العنوان',$supplier->address??'-','fa-map-marker-alt'],['نوع الدفع',['cash'=>'نقدي','credit'=>'آجل','mixed'=>'مختلط'][$supplier->payment_type]??'-','fa-credit-card']] as [$lbl,$val,$icon])
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center"><i class="fas {{ $icon }} text-slate-400 text-xs"></i></div>
                <div><p class="text-slate-500 text-xs">{{ $lbl }}</p><p class="text-white">{{ $val }}</p></div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card p-6">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2"><i class="fas fa-chart-bar text-blue-400"></i> الملخص المالي</h3>
        <div class="space-y-3 text-sm mt-4">
            <div class="flex justify-between"><span class="text-slate-400">إجمالي المشتريات</span><span class="text-white font-bold">{{ number_format($totalPurchases,0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">المدفوعات</span><span class="text-green-400 font-bold">{{ number_format($totalPayments,0) }}</span></div>
            <div class="flex justify-between border-t border-slate-700 pt-2"><span class="text-slate-400">الرصيد المستحق</span><span class="{{ $supplier->balance > 0 ? 'text-red-400':'text-green-400' }} font-bold text-lg">{{ $supplier->balance > 0 ? '-':' ' }}{{ number_format(abs($supplier->balance),0) }}</span></div>
        </div>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('supplier-payments.create') }}?supplier_id={{ $supplier->id }}" class="flex-1 btn-primary text-white px-4 py-2 rounded-lg text-sm font-bold block text-center"><i class="fas fa-hand-holding-usd"></i> تسجيل دفعة</a>
            <a href="{{ route('supplier-payments.create') }}?supplier_id={{ $supplier->id }}&payment_type=deduction" class="flex-1 btn-secondary text-white bg-red-500 px-4 py-2 rounded-lg text-sm font-bold block text-center"><i class="fas fa-minus-circle"></i> خصم</a>
        </div>
    </div>

    <div class="card p-6">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2"><i class="fas fa-info-circle text-amber-400"></i> معلومات إضافية</h3>
        <div class="mt-4 text-sm space-y-2">
            <p class="text-slate-400">عدد الفواتير: <span class="text-white font-bold">{{ $purchases->total() }}</span></p>
            @if($supplier->notes)<div class="bg-slate-800/50 rounded-lg p-3 text-slate-300 mt-3">{{ $supplier->notes }}</div>@endif
        </div>
    </div>
</div>

{{-- Purchases Table --}}
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700"><h3 class="text-white font-bold flex items-center gap-2"><i class="fas fa-shopping-cart text-slate-400"></i> فواتير المشتريات</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-800/50 border-b border-slate-700">
                @foreach(['#','التاريخ','رقم الفاتورة','الإجمالي','الدفع','الحالة',''] as $h)
                <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($purchases as $p)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $p->id }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $p->purchase_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $p->invoice_number ?? '-' }}</td>
                    <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($p->total_amount,0) }}</td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ ['cash'=>'نقدي','credit'=>'آجل','mixed'=>'مختلط'][$p->payment_type]??'' }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $p->status==='paid'?'badge-green':($p->status==='partial'?'badge-yellow':'badge-red') }}">{{ ['pending'=>'معلق','partial'=>'جزئي','paid'=>'مسدد'][$p->status]??$p->status }}</span></td>
                    <td class="px-4 py-3"><a href="{{ route('supplier-purchases.show', $p) }}" class="text-blue-400 hover:text-blue-300 text-xs"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">لا توجد مشتريات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $purchases->links() }}
    </div>
    @endif
</div>

{{-- Payments Table --}}
<div class="card overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-slate-700"><h3 class="text-white font-bold flex items-center gap-2"><i class="fas fa-hand-holding-usd text-green-400"></i> سجل المدفوعات والخصومات</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-800/50 border-b border-slate-700">
                @foreach(['التاريخ','النوع','المبلغ','طريقة الدفع','ملاحظات'] as $h)
                <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($payments as $pay)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $pay->payment_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        @if(($pay->payment_type ?? 'payment') === 'deduction')
                            <span class="badge badge-purple">خصم</span>
                        @else
                            <span class="badge badge-green">دفعة</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-green-400 font-bold">{{ number_format($pay->amount,0) }}</td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ ['cash'=>'نقدي','bank_transfer'=>'تحويل بنكي','check'=>'شيك'][$pay->payment_method]??$pay->payment_method }}</td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $pay->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">لا توجد مدفوعات أو خصومات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
