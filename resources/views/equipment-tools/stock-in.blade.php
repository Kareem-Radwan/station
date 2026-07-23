@extends('layouts.app')

@section('title', 'إدخال كمية')

@section('content')

<div class="max-w-3xl mx-auto">

    @include('partials.page-header', [
        'title' => 'إدخال كمية - ' . $equipmentTool->name,
        'icon' => 'fa-plus-circle',
        'backRoute' => route('equipment-tools.show', $equipmentTool),
    ])

    {{-- Current Summary --}}
    <div class="card p-6 mb-6">

        <div class="grid md:grid-cols-3 gap-6">

            <div class="stat-card rounded-xl p-5">
                <div class="text-slate-400 text-sm mb-2">
                    الرصيد الحالي
                </div>

                <div class="text-2xl font-bold text-blue-400">
                    {{ number_format($equipmentTool->quantity,2) }}
                </div>

                <div class="text-slate-400 text-sm">
                    {{ $equipmentTool->unit }}
                </div>
            </div>

            <div class="stat-card rounded-xl p-5">
                <div class="text-slate-400 text-sm mb-2">
                    القيمة الحالية
                </div>

                <div class="text-2xl font-bold text-amber-400">
                    {{ number_format($equipmentTool->total_value,0) }}
                </div>

                <div class="text-slate-400 text-sm">
                    
                </div>
            </div>

            <div class="stat-card rounded-xl p-5 flex flex-col justify-center items-center">
                <div class="w-14 h-14 rounded-full bg-blue-500/20 flex items-center justify-center mb-3">
                    <i class="fas fa-box text-blue-400 text-2xl"></i>
                </div>

                <div class="font-semibold">
                    {{ $equipmentTool->name }}
                </div>
            </div>

        </div>

    </div>

    <div class="card p-8">

        <form action="{{ route('equipment-tools.stock-in.store',$equipmentTool) }}" method="POST" class="space-y-6">

            @csrf

            <div>

                <h3 class="text-amber-400 font-semibold mb-5 flex items-center gap-2">
                    <i class="fas fa-arrow-down"></i>
                    بيانات الإدخال
                </h3>

                <div class="grid md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm text-slate-300 mb-2">
                            الكمية المضافة
                        </label>

                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            value="{{ old('quantity') }}"
                            min="0.01"
                            step="0.01"
                            class="input-field w-full px-4 py-3 @error('quantity') border-red-500 @enderror"
                            required>

                        <p class="text-xs text-slate-500 mt-2">
                            الوحدة: {{ $equipmentTool->unit }}
                        </p>

                        @error('quantity')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>

                        <label class="block text-sm text-slate-300 mb-2">
                            التكلفة الإجمالية
                        </label>

                        <div class="relative">

                            <input
                                type="number"
                                name="total_cost"
                                id="total_cost"
                                value="{{ old('total_cost') }}"
                                min="0"
                                step="0.01"
                                class="input-field w-full px-4 py-3 pr-16 @error('total_cost') border-red-500 @enderror"
                                required>

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
                                
                            </span>

                        </div>

                        <p class="text-xs text-slate-500 mt-2">
                            سيتم خصم القيمة من الخزينة
                        </p>

                        @error('total_cost')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror

                    </div>

                    <div>

                        <label class="block text-sm text-slate-300 mb-2">
                            تاريخ الإدخال
                        </label>

                        <input
                            type="date"
                            name="movement_date"
                            value="{{ old('movement_date', now()->format('Y-m-d')) }}"
                            class="input-field w-full px-4 py-3 @error('movement_date') border-red-500 @enderror"
                            required>

                        @error('movement_date')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror

                    </div>

                    <div></div>

                    <div class="md:col-span-2">

                        <label class="block text-sm text-slate-300 mb-2">
                            ملاحظات
                        </label>

                        <textarea
                            name="notes"
                            rows="4"
                            class="input-field w-full px-4 py-3 resize-none @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>

                        @error('notes')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                        @enderror

                    </div>

                </div>

            </div>

            <div class="border-t border-slate-700 pt-6 flex justify-end gap-3">

                <a href="{{ route('equipment-tools.show',$equipmentTool) }}"
                   class="px-6 py-3 rounded-lg border border-slate-600 hover:border-slate-500 hover:bg-slate-700 transition flex items-center gap-2">

                    <i class="fas fa-times"></i>

                    إلغاء

                </a>

                <button
                    type="submit"
                    class="btn-primary px-8 py-3 rounded-lg text-white flex items-center gap-2">

                    <i class="fas fa-check-circle"></i>

                    تأكيد الإدخال

                </button>

            </div>

        </form>

    </div>

</div>

@endsection