@extends('layouts.app')
@section('title', 'إضافة معاملة')
@section('content')

    @include('partials.page-header', [
        'title' => 'إضافة معاملة - ' . $neighboringStation->name,
        'icon' => 'fa-exchange-alt',
    ])

    <div class="card p-6 max-w-2xl">
        <form action="{{ route('neighboring-stations.store-transaction', $neighboringStation) }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">نوع المعاملة *</label>
                        <select name="transaction_type" required class="input-field w-full px-4 py-2">
                            <option value="">اختر نوع المعاملة</option>
                            <option value="rent_equipment">تأجير معدات</option>
                            <option value="rent_vehicle">تأجير مركبة</option>
                            <option value="borrow_material">استعارة مواد</option>
                            <option value="borrow_inventory">استعارة من المخزون</option>
                            <option value="sell_concrete">بيع خرسانة</option>
                            <option value="service">خدمة</option>
                        </select>
                        @error('transaction_type')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">اتجاه المعاملة *</label>
                        <select name="direction" required class="input-field w-full px-4 py-2">
                            <option value="">اختر الاتجاه</option>
                            <option value="incoming">وارد (نستلم نحن)</option>
                            <option value="outgoing">صادر (ندفع نحن)</option>
                        </select>
                        @error('direction')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">تاريخ المعاملة *</label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" 
                            required class="input-field w-full px-4 py-2">
                        @error('transaction_date')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">المبلغ الإجمالي *</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" 
                            required class="input-field w-full px-4 py-2">
                        @error('amount')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">الوصف *</label>
                    <textarea name="description" rows="3" required class="input-field w-full px-4 py-2">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">رقم المرجع</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                        class="input-field w-full px-4 py-2">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">حالة الدفع *</label>
                        <select name="payment_status" required class="input-field w-full px-4 py-2" onchange="updatePaidAmount(this)">
                            <option value="pending">معلق</option>
                            <option value="partial">دفع جزئي</option>
                            <option value="paid">مدفوع بالكامل</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">المبلغ المدفوع *</label>
                        <input type="number" name="paid_amount" id="paid_amount" value="{{ old('paid_amount', 0) }}" 
                            step="0.01" min="0" required class="input-field w-full px-4 py-2">
                        @error('paid_amount')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-4 py-2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    حفظ
                </button>
                <a href="{{ route('neighboring-stations.show', $neighboringStation) }}" 
                    class="btn bg-slate-700 hover:bg-slate-600 px-6 py-2 rounded-lg text-white">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function updatePaidAmount(select) {
            const amountInput = document.querySelector('[name="amount"]');
            const paidAmountInput = document.getElementById('paid_amount');
            
            if (select.value === 'paid') {
                paidAmountInput.value = amountInput.value || 0;
            } else if (select.value === 'pending') {
                paidAmountInput.value = 0;
            }
        }
    </script>
    @endpush

@endsection
