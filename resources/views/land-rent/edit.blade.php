@extends('layouts.app')
@section('title', 'تعديل دفعة إيجار')
@section('content')

@include('partials.page-header', ['title' => 'تعديل إيجار: ' . \Carbon\Carbon::create()->month($landRent->month)->translatedFormat('F') . ' ' . $landRent->year, 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('land-rent.update', $landRent) }}" method="POST" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الشهر <span class="text-red-400">*</span></label>
                <select name="month" required class="input-field w-full px-3 py-2.5 text-sm">
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ old('month',$landRent->month)==$m?'selected':'' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">السنة <span class="text-red-400">*</span></label>
                <select name="year" required class="input-field w-full px-3 py-2.5 text-sm">
                    @for($y=now()->year;$y>=now()->year-3;$y--)
                    <option value="{{ $y }}" {{ old('year',$landRent->year)==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المبلغ <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount',$landRent->amount) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تاريخ الدفع</label>
                <input type="date" name="payment_date" value="{{ old('payment_date', $landRent->payment_date?->toDateString()) }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الحالة</label>
                <select name="status" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="paid" {{ $landRent->status==='paid'?'selected':'' }}>مسدد</option>
                    <option value="pending" {{ $landRent->status==='pending'?'selected':'' }}>معلق</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes',$landRent->notes) }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('land-rent.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection
