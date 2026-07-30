@extends('layouts.app')
@section('title', 'تعديل دفعة مساهم')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('contributors.index') }}" class="text-slate-400 hover:text-white text-sm">
                    المساهمون
                </a>

                <i class="fas fa-chevron-left text-slate-600 text-xs"></i>

                <a href="{{ route('contributors.show', $contributorPayment->contributor) }}" class="text-slate-400 hover:text-white text-sm">
                    {{ $contributorPayment->contributor->name }}
                </a>

                <i class="fas fa-chevron-left text-slate-600 text-xs"></i>

                <span class="text-white font-bold">
                    تعديل دفعة
                </span>
            </div>

            <p class="text-slate-400 text-sm">
                تعديل بيانات الدفعة رقم #{{ $contributorPayment->id }}
            </p>
        </div>
    </div>

    <form action="{{ route('contributor-payments.update', $contributorPayment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="max-w-4xl">

            <div class="card p-6">

                <h3 class="text-white font-bold flex items-center gap-2 border-b border-slate-700 pb-3 mb-6">
                    <i class="fas fa-money-bill-wave text-amber-400"></i>
                    بيانات الدفعة
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Contributor (Read-only) --}}
                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            المساهم
                        </label>

                        <div class="bg-slate-700/50 text-slate-300 px-4 py-3 rounded-lg">
                            {{ $contributorPayment->contributor->name }}
                        </div>

                        <p class="text-slate-500 text-xs mt-1">
                            لا يمكن تغيير المساهم بعد إنشاء الدفعة
                        </p>
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            المبلغ
                        </label>

                        <input type="number" step="0.01" min="0.01" name="amount" 
                            value="{{ old('amount', $contributorPayment->amount) }}"
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

                        <input type="date" name="payment_date" 
                            value="{{ old('payment_date', $contributorPayment->payment_date->format('Y-m-d')) }}"
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

                            <option value="cash" {{ old('payment_method', $contributorPayment->payment_method) == 'cash' ? 'selected' : '' }}>
                                نقدي
                            </option>

                            <option value="bank_transfer" {{ old('payment_method', $contributorPayment->payment_method) == 'bank_transfer' ? 'selected' : '' }}>
                                تحويل بنكي
                            </option>

                            <option value="check" {{ old('payment_method', $contributorPayment->payment_method) == 'check' ? 'selected' : '' }}>
                                شيك
                            </option>

                        </select>

                        @error('payment_method')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reference --}}
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">
                            رقم المرجع / الشيك
                        </label>

                        <input type="text" name="reference_number" 
                            value="{{ old('reference_number', $contributorPayment->reference_number) }}"
                            class="input-field w-full px-4 py-3" placeholder="اختياري">
                    </div>

                    {{-- Notes --}}
                    <div class="md:col-span-2">
                        <label class="block text-slate-300 text-sm mb-2">
                            ملاحظات
                        </label>

                        <textarea name="notes" rows="4" class="input-field w-full px-4 py-3" placeholder="ملاحظات إضافية">{{ old('notes', $contributorPayment->notes) }}</textarea>
                    </div>

                </div>

            </div>

            <div class="flex items-center gap-3 mt-6">

                <button type="submit" class="btn-primary text-white px-6 py-3 rounded-lg font-bold">

                    <i class="fas fa-save"></i>
                    تحديث الدفعة
                </button>

                <a href="{{ route('contributors.show', $contributorPayment->contributor) }}"
                    class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-3 rounded-lg">
                    إلغاء
                </a>

            </div>

        </div>

    </form>

@endsection
