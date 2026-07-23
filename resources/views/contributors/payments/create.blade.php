@extends('layouts.app')
@section('title', 'تسجيل دفعة مساهم')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('contributors.index') }}" class="text-slate-400 hover:text-white text-sm">
                    المساهمون
                </a>

                <i class="fas fa-chevron-left text-slate-600 text-xs"></i>

                <span class="text-white font-bold">
                    تسجيل دفعة
                </span>
            </div>

            <p class="text-slate-400 text-sm">
                إضافة دفعة جديدة للمساهم وربطها بالخزينة
            </p>
        </div>
    </div>

    <form action="{{ route('contributor-payments.store') }}" method="POST">
        @csrf

        <div class="max-w-4xl">

            <div class="card p-6">

                <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3 mb-6">
                    <i class="fas fa-money-bill-wave text-amber-400"></i>
                    بيانات الدفعة
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Contributor --}}
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            المساهم
                        </label>

                        <select name="contributor_id" required class="input-field w-full px-4 py-3">

                            <option value="">اختر المساهم</option>

                            @foreach ($contributors as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('contributor_id', $contributor?->id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach

                        </select>

                        @error('contributor_id')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            المبلغ
                        </label>

                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}"
                            required class="input-field w-full px-4 py-3" placeholder="0.00">

                        @error('amount')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            تاريخ الدفعة
                        </label>

                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                            required class="input-field w-full px-4 py-3">

                        @error('payment_date')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Method --}}
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            طريقة الدفع
                        </label>

                        <select name="payment_method" required class="input-field w-full px-4 py-3">

                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                نقدي
                            </option>

                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>
                                تحويل بنكي
                            </option>

                            <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>
                                شيك
                            </option>

                        </select>

                        @error('payment_method')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reference --}}
                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            رقم المرجع / الشيك
                        </label>

                        <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                            class="input-field w-full px-4 py-3" placeholder="اختياري">
                    </div>

                    {{-- Notes --}}
                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            ملاحظات
                        </label>

                        <textarea name="notes" rows="4" class="input-field w-full px-4 py-3" placeholder="ملاحظات إضافية">{{ old('notes') }}</textarea>
                    </div>

                </div>

            </div>

            <div class="flex items-center gap-3 mt-6">

                <button type="submit" class="btn-primary text-white px-6 py-3 rounded-lg font-bold">

                    <i class="fas fa-save"></i>
                    حفظ الدفعة
                </button>

                <a href="{{ $contributor ? route('contributors.show', $contributor) : route('contributors.index') }}"
                    class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-3 rounded-lg">
                    إلغاء
                </a>

            </div>

        </div>

    </form>

@endsection
