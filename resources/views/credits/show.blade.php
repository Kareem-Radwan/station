@extends('layouts.app')
@section('title', 'تفاصيل الدين')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('credits.index') }}" class="text-slate-400 hover:text-white text-sm">الديون</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">دين #{{ $credit->id }}</span>
        </div>
        <span class="badge badge-{{ ['pending'=>'yellow','paid'=>'green','overdue'=>'red'][$credit->status]??'gray' }}">
            {{ ['pending'=>'معلق','paid'=>'مسدد','overdue'=>'متأخر'][$credit->status]??$credit->status }}
        </span>
    </div>
    @if($credit->status !== 'paid')
    <div class="flex gap-3">
        <form action="{{ route('credits.mark-paid', $credit) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-check-double"></i> تحديد كمسدد بالكامل
            </button>
        </form>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="card p-6 space-y-4">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i> بيانات الدين
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-slate-400">الجهة المدين</span>
                <span class="text-white font-medium">
                    @if($credit->creditable_type === 'customer' && $credit->creditable)
                        <a href="{{ route('customers.show', $credit->creditable) }}" class="text-blue-400">
                            {{ $credit->creditable->name }} <span class="text-xs text-slate-500">(عميل)</span>
                        </a>
                    @elseif($credit->creditable_type === 'supplier' && $credit->creditable)
                        <a href="{{ route('suppliers.show', $credit->creditable) }}" class="text-blue-400">
                            {{ $credit->creditable->name }} <span class="text-xs text-slate-500">(مورد)</span>
                        </a>
                    @else
                        <span class="text-slate-500">-</span>
                    @endif
                </span>
            </div>
            @if($credit->reference_type === 'order')
            <div class="flex justify-between"><span class="text-slate-400">الطلب المرتبط</span>
                <a href="{{ route('orders.show', $credit->reference_id) }}" class="text-blue-400">#{{ $credit->reference_id }}</a>
            @elseif($credit->reference_type === 'purchase')
            <div class="flex justify-between"><span class="text-slate-400">الفاتورة المرتبطة</span>
                <a href="{{ route('supplier-purchases.show', $credit->reference_id) }}" class="text-blue-400">#{{ $credit->reference_id }}</a>
            </div>
            @endif
            @if($credit->notes)
            <div class="mt-2 pt-2 border-t border-slate-700">
                <span class="text-slate-400 block mb-1">الوصف / الملاحظات:</span>
                <p class="text-slate-300">{{ $credit->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-coins text-amber-400"></i> المالية
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-slate-400">المبلغ الإجمالي</span><span class="text-white font-bold text-lg">{{ number_format($credit->amount,0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">المسدد</span><span class="text-green-400">{{ number_format($credit->paid_amount,0) }}</span></div>
            <div class="flex justify-between border-t border-slate-700 pt-2">
                <span class="text-slate-400 font-bold">المتبقي</span>
                <span class="text-red-400 font-bold text-xl">{{ number_format($credit->remaining_amount, 0) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
