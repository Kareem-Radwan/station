@extends('layouts.app')

@section('title', 'صرف كمية')

@section('content')

    <div class="max-w-3xl mx-auto">

        @include('partials.page-header', [
            'title' => 'صرف كمية - ' . $equipmentTool->name,
            'icon' => 'fa-minus-circle',
            'backRoute' => route('equipment-tools.show', $equipmentTool),
        ])

        {{-- Current Balance --}}
        <div class="stat-card rounded-xl p-6 mb-6">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-slate-400 text-sm">
                        الرصيد الحالي
                    </p>

                    <div
                        class="mt-3 text-4xl font-black {{ $equipmentTool->quantity > 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format($equipmentTool->quantity, 2) }}
                    </div>

                    <div class="text-slate-300 mt-2">
                        {{ $equipmentTool->unit }}
                    </div>

                </div>

                <div>

                    <p class="text-slate-400 text-sm text-right">
                        سعر الوحدة
                    </p>

                    <div class="mt-3 text-2xl font-bold text-blue-400 text-right">
                        {{ number_format($equipmentTool->price_per_unit, 0) }}
                    </div>

                </div>

                <div class="w-16 h-16 rounded-2xl bg-red-500/20 flex items-center justify-center">
                    <i class="fas fa-box-open text-red-400 text-3xl"></i>
                </div>

            </div>

        </div>

        @if ($equipmentTool->quantity <= 0)
            <div class="card p-8 text-center border border-red-500/30">

                <div class="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center mx-auto mb-5">
                    <i class="fas fa-exclamation-triangle text-red-400 text-4xl"></i>
                </div>

                <h2 class="text-white text-xl font-bold mb-2">
                    لا يمكن صرف كمية
                </h2>

                <p class="text-slate-400">
                    الرصيد الحالي يساوي صفر ولا توجد كمية متاحة للصرف.
                </p>

                <div class="mt-6">
                    <a href="{{ route('equipment-tools.stock-in', $equipmentTool) }}"
                        class="btn-accent inline-flex items-center gap-2 px-5 py-3 rounded-lg text-white font-semibold">

                        <i class="fas fa-plus-circle"></i>

                        إضافة كمية للمخزون

                    </a>
                </div>

            </div>
        @else
            <div class="card p-8">

                <form action="{{ route('equipment-tools.stock-out.store', $equipmentTool) }}" method="POST"
                    id="stockOutForm">

                    @csrf

                    {{-- Quantity --}}
                    <div class="mb-6">

                        <label class="block text-sm font-semibold text-slate-300 mb-2">
                            الكمية المصروفة
                            <span class="text-red-400">*</span>
                        </label>

                        <input type="number" id="quantity" name="quantity" step="0.01" min="0.01"
                            max="{{ $equipmentTool->quantity }}" value="{{ old('quantity') }}"
                            class="input-field w-full px-4 py-3 @error('quantity') border-red-500 @enderror" required>

                        <div class="mt-2 text-xs text-slate-500">
                            الحد الأقصى:
                            {{ number_format($equipmentTool->quantity, 2) }}
                            {{ $equipmentTool->unit }}
                        </div>

                        @error('quantity')
                            <p class="text-red-400 text-sm mt-2">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Calculation --}}
                    <div class="rounded-xl border border-slate-700 bg-slate-900/50 p-5 mb-6">

                        <div class="flex justify-between py-2">

                            <span class="text-slate-400">
                                تكلفة الصرف
                            </span>

                            <span id="calculated_cost" class="text-red-400 font-bold text-lg">
                                0
                            </span>

                        </div>

                        <div class="border-t border-slate-700 my-3"></div>

                        <div class="flex justify-between py-2">

                            <span class="text-slate-400">
                                الرصيد بعد الصرف
                            </span>

                            <span id="new_balance" class="text-green-400 font-bold text-lg">

                                {{ number_format($equipmentTool->quantity, 2) }}
                                {{ $equipmentTool->unit }}

                            </span>

                        </div>

                    </div>


                    {{-- Date --}}
                    <div class="mb-6">

                        <label class="block text-sm font-semibold text-slate-300 mb-2">

                            تاريخ الصرف

                            <span class="text-red-400">*</span>

                        </label>

                        <input type="date" name="movement_date" value="{{ old('movement_date', date('Y-m-d')) }}"
                            class="input-field w-full px-4 py-3 @error('movement_date') border-red-500 @enderror" required>

                        @error('movement_date')
                            <p class="text-red-400 text-sm mt-2">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Notes --}}
                    <div class="mb-8">

                        <label class="block text-sm font-semibold text-slate-300 mb-2">

                            سبب الصرف

                        </label>

                        <textarea name="notes" rows="4" placeholder="مثال: تغيير زيت اللودر أو استهلاك في الصيانة..."
                            class="input-field w-full px-4 py-3 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>

                        @error('notes')
                            <p class="text-red-400 text-sm mt-2">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Buttons --}}
                    <div class="flex gap-4">

                        <button type="submit"
                            class="flex-1 rounded-lg bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 transition py-3 font-bold text-white">

                            <i class="fas fa-minus-circle mr-2"></i>

                            تأكيد الصرف

                        </button>

                        <a href="{{ route('equipment-tools.show', $equipmentTool) }}"
                            class="px-6 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-white font-semibold">

                            <i class="fas fa-arrow-right ml-2"></i>

                            رجوع

                        </a>

                    </div>

                </form>

            </div>
        @endif

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                const quantityInput = document.getElementById('quantity');

                if (!quantityInput) return;

                const costLabel = document.getElementById('calculated_cost');
                const balanceLabel = document.getElementById('new_balance');

                const currentQuantity = {{ $equipmentTool->quantity }};
                const unitPrice = {{ $equipmentTool->price_per_unit }};

                function updateValues() {

                    let quantity = parseFloat(quantityInput.value) || 0;

                    if (quantity > currentQuantity) {

                        quantity = currentQuantity;

                        quantityInput.value = currentQuantity;

                    }

                    const total = quantity * unitPrice;

                    const balance = currentQuantity - quantity;

                    costLabel.textContent =
                        total.toLocaleString() + ' IQD';

                    balanceLabel.textContent =
                        balance.toFixed(2) + ' {{ $equipmentTool->unit }}';

                }

                quantityInput.addEventListener('input', updateValues);

                updateValues();

            });
        </script>
    @endpush

@endsection
