@extends('layouts.app')
@section('title', 'تفاصيل الطلب #'.$order->id)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('orders.index') }}" class="text-slate-400 hover:text-white text-sm">الطلبات</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">طلب #{{ $order->id }}</span>
        </div>
        <div class="flex gap-2 mt-1">
            <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
            <span class="badge {{ $order->concrete_type==='operational'?'badge-blue':'badge-green' }}">{{ $order->concrete_type_label }}</span>
        </div>
    </div>
    <div class="flex gap-3">
        @if($order->status === 'pending' || $order->status === 'scheduled')
        <form action="{{ route('orders.update-status', $order) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="delivered">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-check-circle"></i> تأكيد التسليم
            </button>
        </form>
        @endif
        <a href="{{ route('orders.edit', $order) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
        <form action="{{ route('orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟ سيتم حذف جميع تكاليف المواد والحركات المرتبطة من الخزينة والمخزون')">
            @csrf @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-trash"></i> حذف
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Order Info --}}
    <div class="card p-6 space-y-4 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-file-alt text-amber-400"></i> بيانات الطلب
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between"><span class="text-slate-400">العميل</span>
                <a href="{{ route('customers.show',$order->customer) }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ $order->customer->name }}</a>
            </div>
            <div class="flex justify-between"><span class="text-slate-400">الكمية</span><span class="text-white font-bold text-lg">{{ $order->quantity_m3 }} م³</span></div>
            <div class="flex justify-between"><span class="text-slate-400">تاريخ التسليم</span><span class="text-white">{{ $order->delivery_date->format('d/m/Y') }}</span></div>
            @if($order->delivery_time)
            <div class="flex justify-between"><span class="text-slate-400">وقت التسليم</span><span class="text-white">{{ $order->delivery_time }}</span></div>
            @endif
            @if($order->location)
            <div class="flex justify-between"><span class="text-slate-400">الموقع</span><span class="text-white text-left">{{ $order->location }}</span></div>
            @endif
            @if($order->concrete_mix_id)
            <div class="flex justify-between"><span class="text-slate-400">الخلطة</span><span class="text-white">{{ $order->concreteMix?->name }}</span></div>
            @endif
        </div>
    </div>

    {{-- Cement Info (operational only) --}}
    @if($order->concrete_type === 'operational')
    <div class="card p-6 space-y-4 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-weight text-blue-400"></i> الاسمنت
        </h3>
        <div class="text-center py-4">
            <p class="text-4xl font-bold text-amber-400">{{ number_format($order->cement_deducted ?? 0, 1) }}</p>
            <p class="text-slate-400 mt-1">طن مخصوم</p>
        </div>
        <div class="bg-blue-900/20 border border-blue-500/30 rounded-xl p-3 text-xs text-slate-300">
            <p>الكمية: {{ $order->quantity_m3 }} م³ خرسانة</p>
        </div>
    </div>
    @else
    {{-- Financial Info (complete type) --}}
    <div class="card p-6 space-y-4 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-coins text-blue-400"></i> المالية
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between"><span class="text-slate-400">سعر الوحدة</span><span class="text-white">{{ $order->unit_price ? number_format($order->unit_price,0) : '-' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">الإجمالي</span><span class="text-amber-400 font-bold text-xl">{{ $order->total_amount ? number_format($order->total_amount,0) : '-' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">نوع الدفع</span><span class="text-white">{{ $order->payment_type_label }}</span></div>
            @if($order->cash_amount)<div class="flex justify-between"><span class="text-slate-400">نقدي</span><span class="text-green-400">{{ number_format($order->cash_amount,0) }}</span></div>@endif
            @if($order->credit_amount)<div class="flex justify-between"><span class="text-slate-400">آجل</span><span class="text-red-400">{{ number_format($order->credit_amount,0) }}</span></div>@endif
        </div>
    </div>
    @endif

    {{-- Notes --}}
    <div class="card p-6 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2 mb-4">
            <i class="fas fa-sticky-note text-slate-400"></i> ملاحظات
        </h3>
        <p class="text-slate-300">{{ $order->notes ?? 'لا توجد ملاحظات' }}</p>
        @if($order->status !== 'delivered' && $order->status !== 'cancelled')
        <div class="mt-4 pt-4 border-t border-slate-700 space-y-2">
            <p class="text-slate-400 text-xs mb-2">تغيير الحالة السريع:</p>
            @foreach(['scheduled'=>'مجدول','cancelled'=>'إلغاء'] as $s=>$l)
            <form action="{{ route('orders.update-status', $order) }}" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $s }}">
                <button type="submit" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-600 text-slate-400 hover:border-amber-500/50 hover:text-amber-400 transition">{{ $l }}</button>
            </form>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
