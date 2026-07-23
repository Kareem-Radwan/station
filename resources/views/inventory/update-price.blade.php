@extends('layouts.app')
@section('title', 'تحديث السعر - '.$item->name_ar)
@section('content')

@include('partials.page-header', ['title' => 'تحديث سعر الوحدة: '.$item->name_ar, 'icon' => 'fa-tag'])

<div class="max-w-2xl">
    <div class="card p-4 mb-6 flex items-center gap-4 border border-amber-500/30">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
            <i class="fas fa-tag text-amber-400 text-xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-slate-400 text-xs">السعر الحالي</p>
            <p class="text-2xl font-bold text-amber-400">
                @if($item->price_per_unit > 0)
                    {{ number_format($item->price_per_unit, 2) }} 
                    <span class="text-sm font-normal text-slate-400">جنيه / {{ $item->unit }}</span>
                @else
                    <span class="text-slate-500 text-base">غير محدد</span>
                @endif
            </p>
        </div>
        <div>
            <p class="text-slate-400 text-xs">المخزون الحالي</p>
            <p class="text-xl font-bold text-green-400">{{ number_format($item->current_stock, 1) }} <span class="text-sm font-normal text-slate-400">{{ $item->unit }}</span></p>
        </div>
    </div>

    <form action="{{ route('inventory.update-price.store', $item) }}" method="POST" class="card p-6 space-y-5">
        @csrf
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">سعر الوحدة الجديد (جنيه) <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="price_per_unit" min="0" 
                    value="{{ old('price_per_unit', $item->price_per_unit ?? 0) }}" 
                    required autofocus
                    class="input-field w-full px-3 py-2.5 text-sm" 
                    placeholder="أدخل السعر الجديد">
                @error('price_per_unit')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-slate-500 text-xs mt-1.5">
                    <i class="fas fa-info-circle"></i> 
                    سيتم استخدام هذا السعر في حساب تكاليف المواد عند الطلبات الجديدة
                </p>
            </div>

            @if($item->price_per_unit > 0)
            <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3">
                <p class="text-slate-400 text-xs mb-2">معلومات إضافية:</p>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-400">السعر القديم:</span>
                    <span class="text-slate-300 font-bold">{{ number_format($item->price_per_unit, 2) }} جنيه</span>
                </div>
                <div class="flex justify-between items-center text-sm mt-1">
                    <span class="text-slate-400">قيمة المخزون الحالي:</span>
                    <span class="text-amber-400 font-bold">{{ number_format($item->current_stock * $item->price_per_unit, 2) }} جنيه</span>
                </div>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check"></i> تحديث السعر
            </button>
            <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection
