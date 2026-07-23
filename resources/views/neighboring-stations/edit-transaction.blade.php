@extends('layouts.app')
@section('title', 'تعديل معاملة')
@section('content')

    @include('partials.page-header', [
        'title' => 'تعديل معاملة - ' . $neighboringStation->name,
        'icon' => 'fa-exchange-alt',
    ])

    <div class="card p-6 max-w-2xl">
        <form action="{{ route('neighboring-stations.update-transaction', [$neighboringStation, $transaction]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">نوع المعاملة *</label>
                        <select name="transaction_type" required class="input-field w-full px-4 py-2">
                            <option value="">اختر نوع المعاملة</option>
                            <option value="rent_equipment" {{ old('transaction_type', $transaction->transaction_type) == 'rent_equipment' ? 'selected' : '' }}>تأجير معدات</option>
                            <option value="rent_vehicle" {{ old('transaction_type', $transaction->transaction_type) == 'rent_vehicle' ? 'selected' : '' }}>تأجير مركبة</option>
                            <option value="borrow_material" {{ old('transaction_type', $transaction->transaction_type) == 'borrow_material' ? 'selected' : '' }}>استعارة مواد</option>
                            <option value="borrow_inventory" {{ old('transaction_type', $transaction->transaction_type) == 'borrow_inventory' ? 'selected' : '' }}>استعارة من المخزون</option>
                            <option value="sell_concrete" {{ old('transaction_type', $transaction->transaction_type) == 'sell_concrete' ? 'selected' : '' }}>بيع خرسانة</option>
                            <option value="service" {{ old('transaction_type', $transaction->transaction_type) == 'service' ? 'selected' : '' }}>خدمة</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">اتجاه المعاملة *</label>
                        <select name="direction" required class="input-field w-full px-4 py-2">
                            <option value="incoming" {{ old('direction', $transaction->direction) == 'incoming' ? 'selected' : '' }}>وارد (نستلم نحن)</option>
                            <option value="outgoing" {{ old('direction', $transaction->direction) == 'outgoing' ? 'selected' : '' }}>صادر (ندفع نحن)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">تاريخ المعاملة *</label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" 
                            required class="input-field w-full px-4 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">المبلغ الإجمالي *</label>
                        <input type="number" name="amount" value="{{ old('amount', $transaction->amount) }}" step="0.01" min="0" 
                            required class="input-field w-full px-4 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">الوصف *</label>
                    <textarea name="description" rows="3" required class="input-field w-full px-4 py-2">{{ old('description', $transaction->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">رقم المرجع</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number', $transaction->reference_number) }}"
                        class="input-field w-full px-4 py-2">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">حالة الدفع *</label>
                        <select name="payment_status" required class="input-field w-full px-4 py-2">
                            <option value="pending" {{ old('payment_status', $transaction->payment_status) == 'pending' ? 'selected' : '' }}>معلق</option>
                            <option value="partial" {{ old('payment_status', $transaction->payment_status) == 'partial' ? 'selected' : '' }}>دفع جزئي</option>
                            <option value="paid" {{ old('payment_status', $transaction->payment_status) == 'paid' ? 'selected' : '' }}>مدفوع بالكامل</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2">المبلغ المدفوع *</label>
                        <input type="number" name="paid_amount" value="{{ old('paid_amount', $transaction->paid_amount) }}" 
                            step="0.01" min="0" required class="input-field w-full px-4 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">ملاحظات</label>
                    <textarea name="notes" rows="3" class="input-field w-full px-4 py-2">{{ old('notes', $transaction->notes) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">
                    <i class="fas fa-save ml-2"></i>
                    تحديث
                </button>
                <a href="{{ route('neighboring-stations.show', $neighboringStation) }}" 
                    class="btn bg-slate-700 hover:bg-slate-600 px-6 py-2 rounded-lg text-white">
                    <i class="fas fa-times ml-2"></i>
                    إلغاء
                </a>
            </div>
        </form>
    </div>

@endsection
