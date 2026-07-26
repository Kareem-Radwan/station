@extends('layouts.app')
@section('title', 'تعديل دفعة من عميل')
@section('content')

@include('partials.page-header', ['title' => 'تعديل دفعة من عميل', 'icon' => 'fa-edit'])

<div class="max-w-2xl">
    <form action="{{ route('customer-payments.update', $customerPayment) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="card p-6 space-y-4">
            {{-- Read-only customer info --}}
            <div class="bg-slate-800/30 p-4 rounded-lg border border-slate-700/50">
                <p class="text-sm text-slate-400 mb-1">العميل:</p>
                <p class="text-white font-bold">{{ $customerPayment->customer->name }}</p>
                <p class="text-amber-400 text-xs mt-1">
                    <i class="fas fa-info-circle"></i> لا يمكن تغيير العميل. لتغييره، احذف الدفعة وأنشئها من جديد.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ الدفع <span class="text-red-400">*</span></label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', $customerPayment->payment_date->format('Y-m-d')) }}" required class="input-field w-full px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المبلغ <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $customerPayment->amount) }}" required min="0.01" class="input-field w-full px-3 py-2.5 text-sm">
                    @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">طريقة الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_method" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="cash"          {{ old('payment_method', $customerPayment->payment_method)=='cash'         ?'selected':'' }}>نقدي</option>
                        <option value="bank_transfer" {{ old('payment_method', $customerPayment->payment_method)=='bank_transfer'?'selected':'' }}>تحويل بنكي</option>
                        <option value="check"         {{ old('payment_method', $customerPayment->payment_method)=='check'        ?'selected':'' }}>شيك</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $customerPayment->notes) }}</textarea>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ التعديلات</button>
            <a href="{{ route('customer-payments.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection
