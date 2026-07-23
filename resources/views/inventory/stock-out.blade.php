@extends('layouts.app')
@section('title', 'مخزون صادر - ' . $item->name_ar)
@section('content')

    @include('partials.page-header', [
        'title' => 'صرف من المخزون: ' . $item->name_ar,
        'icon' => 'fa-arrow-up',
    ])

    <div class="max-w-2xl">
        <div class="card p-4 mb-6 flex items-center gap-4 border border-red-500/30">
            <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                <i class="fas fa-boxes text-red-400 text-xl"></i>
            </div>

            <div>
                <p class="text-slate-400 text-xs">الرصيد الحالي</p>
                <p class="text-2xl font-bold {{ $item->isBelowAlert() ? 'text-red-400' : 'text-amber-400' }}">
                    {{ number_format($item->current_stock, 1) }} <span
                        class="text-sm font-normal text-slate-400">{{ $item->unit }}</span>
                </p>
            </div>
        </div>

        <form action="{{ route('inventory.stock-out.store', $item) }}" method="POST" class="card p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الكمية الصادرة ({{ $item->unit }}) <span
                            class="text-red-400">*</span></label>
                    <input type="number" step="0.001" name="quantity" min="0.001" max="{{ $item->current_stock }}"
                        required class="input-field w-full px-3 py-2.5 text-sm" placeholder="أدخل الكمية">
                    @error('quantity')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                    <input type="date" name="date" value="{{ today()->toDateString() }}" required
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">
                        سعر بيع الوحدة <span class="text-red-400">*</span>
                    </label>

                    <div class="relative">
                        <input type="number" step="0.01" min="0" name="price_per_unit"
                            value="{{ old('price_per_unit', $item->price_per_unit ?? 0) }}" required
                            class="input-field w-full px-3 py-2.5 text-sm">

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                            جنيه
                        </span>
                    </div>

                    @error('price_per_unit')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">سبب الصرف / ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm"
                        placeholder="مثال: استخدام في طلب رقم 15"></textarea>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="bg-red-600 hover:bg-red-500 text-white font-bold px-6 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fas fa-minus-circle"></i> صرف من المخزون
                </button>
                <a href="{{ route('inventory.index') }}"
                    class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
