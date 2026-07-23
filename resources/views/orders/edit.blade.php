@extends('layouts.app')
@section('title', 'تعديل طلب #'.$order->id)
@section('content')

@include('partials.page-header', ['title' => 'تعديل طلب #'.$order->id, 'icon' => 'fa-edit'])

<div class="max-w-2xl">
    <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        <div class="card p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ التسليم <span class="text-red-400">*</span></label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', $order->delivery_date->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('delivery_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">وقت التسليم</label>
                    <input type="time" name="delivery_time" value="{{ old('delivery_time', $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : '') }}" class="input-field w-full px-3 py-2.5 text-sm">
                    @error('delivery_time')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الكمية (م³) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.001" name="quantity_m3" value="{{ old('quantity_m3', $order->quantity_m3) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('quantity_m3')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الحالة <span class="text-red-400">*</span></label>
                    <select name="status" class="input-field w-full px-3 py-2.5 text-sm">
                        @foreach(['pending'=>'معلق','scheduled'=>'مجدول','delivered'=>'تم التسليم','cancelled'=>'ملغي'] as $v=>$l)
                        <option value="{{ $v }}" {{ $order->status==$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">الموقع</label>
                    <input type="text" name="location" value="{{ old('location', $order->location) }}" class="input-field w-full px-3 py-2.5 text-sm">
                    @error('location')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $order->notes) }}</textarea>
                    @error('notes')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('orders.show', $order) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection
