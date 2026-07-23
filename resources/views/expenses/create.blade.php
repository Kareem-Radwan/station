@extends('layouts.app')
@section('title', 'إضافة مصروف')
@section('content')

@include('partials.page-header', ['title' => 'إضافة مصروف جديد', 'icon' => 'fa-plus-circle'])

<div class="max-w-xl">
    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
            <div class="flex-1">
                <h3 class="text-red-400 font-bold mb-2">يوجد أخطاء في النموذج:</h3>
                <ul class="text-red-300 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('expenses.store') }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="expense_date" value="{{ old('expense_date', today()->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الفئة <span class="text-red-400">*</span></label>
                <select name="category" id="category-select" required class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="">اختر الفئة</option>
                    @php
                        $allCategories = \App\Models\ExpenseCategory::getAllCategories();
                    @endphp
                    @foreach($allCategories as $cat)
                    <option value="{{ $cat }}" {{ old('category')==$cat?'selected':'' }}>{{ $cat }}</option>
                    @endforeach
                    <option value="__custom__">أخرى (مخصص)...</option>
                </select>
                @error('category')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div id="custom-category-wrapper" class="hidden">
                <label class="block text-slate-400 text-sm mb-1.5">الفئة المخصصة <span class="text-red-400">*</span></label>
                <input type="text" name="custom_category" id="custom-category-input" class="input-field w-full px-3 py-2.5 text-sm" placeholder="اكتب اسم الفئة الجديدة">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">الوصف <span class="text-red-400">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" required class="input-field w-full px-3 py-2.5 text-sm">
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المبلغ <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01" class="input-field w-full px-3 py-2.5 text-sm">
                @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">طريقة الدفع</label>
                <select name="payment_method" id="payment-method" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="cash" {{ old('payment_method')=='cash'?'selected':'' }}>نقدي من الخزينة</option>
                    <option value="transfer" {{ old('payment_method')=='transfer'?'selected':'' }}>تحويل</option>
                    <option value="contributor" {{ old('payment_method')=='contributor'?'selected':'' }}>من حساب المساهم</option>
                </select>
                @error('payment_method')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div id="contributor-select-wrapper" class="hidden">
                <label class="block text-slate-400 text-sm mb-1.5">المساهم <span class="text-red-400">*</span></label>
                <select name="contributor_id" id="contributor-select" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="">-- اختر المساهم --</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('expenses.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment-method');
    const contributorWrapper = document.getElementById('contributor-select-wrapper');
    const contributorSelect = document.getElementById('contributor-select');
    const categorySelect = document.getElementById('category-select');
    const customCategoryWrapper = document.getElementById('custom-category-wrapper');
    const customCategoryInput = document.getElementById('custom-category-input');

    // Fetch contributors when page loads
    fetch('/api/contributors/active')
        .then(response => response.json())
        .then(contributors => {
            contributors.forEach(c => {
                const option = new Option(c.name, c.id);
                contributorSelect.add(option);
            });
        });

    // Handle payment method change
    paymentMethod.addEventListener('change', function() {
        if (this.value === 'contributor') {
            contributorWrapper.classList.remove('hidden');
            contributorSelect.required = true;
        } else {
            contributorWrapper.classList.add('hidden');
            contributorSelect.required = false;
            contributorSelect.value = '';
        }
    });

    // Handle category change for custom option
    categorySelect.addEventListener('change', function() {
        if (this.value === '__custom__') {
            customCategoryWrapper.classList.remove('hidden');
            customCategoryInput.required = true;
            customCategoryInput.focus();
        } else {
            customCategoryWrapper.classList.add('hidden');
            customCategoryInput.required = false;
            customCategoryInput.value = '';
        }
    });
});
</script>
@endsection
