@extends('layouts.app')
@section('title', 'تفاصيل إيجار الأرض')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('land-rent.index') }}" class="text-slate-400 hover:text-white text-sm">إيجار الأرض</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">دفعة {{ \Carbon\Carbon::create()->month($landRent->month)->translatedFormat('F') }} {{ $landRent->year }}</span>
        </div>
        <span class="badge {{ $landRent->status==='paid'?'badge-green':'badge-yellow' }}">{{ $landRent->status==='paid'?'مسدد':'معلق' }}</span>
    </div>
    <div class="flex gap-3">
        @if($landRent->status !== 'paid')
        <form action="{{ route('land-rent.update', $landRent) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="paid">
            <input type="hidden" name="month" value="{{ $landRent->month }}">
            <input type="hidden" name="year" value="{{ $landRent->year }}">
            <input type="hidden" name="amount" value="{{ $landRent->amount }}">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-check-circle"></i> تأكيد السداد
            </button>
        </form>
        @endif
        <a href="{{ route('land-rent.edit',$landRent) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
        <form action="{{ route('land-rent.destroy', $landRent) }}" method="POST" class="inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا العقد ودفعاته المرتبطة من الخزينة؟')">
            @csrf @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-trash"></i> حذف
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card p-6 space-y-4">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i> بيانات الإيجار
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-slate-400">السنة</span><span class="text-white">{{ $landRent->year }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">الشهر</span><span class="text-white">{{ \Carbon\Carbon::create()->month($landRent->month)->translatedFormat('F') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">المبلغ</span><span class="text-amber-400 font-bold text-xl">{{ number_format($landRent->amount,0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">تاريخ الدفع</span><span class="text-white">{{ $landRent->payment_date?->format('d/m/Y') ?? '-' }}</span></div>
        </div>
    </div>
    <div class="card p-6 space-y-4">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3 flex items-center gap-2">
            <i class="fas fa-sticky-note text-amber-400"></i> ملاحظات
        </h3>
        <p class="text-slate-300 text-sm">{{ $landRent->notes ?? 'لا توجد ملاحظات' }}</p>
    </div>
</div>
@endsection
