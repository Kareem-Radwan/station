@extends('layouts.app')
@section('title', 'تعديل مصروف')
@section('content')

@include('partials.page-header', ['title' => 'تعديل مصروف', 'icon' => 'fa-edit'])

<div class="max-w-xl">
    <form action="{{ route('expenses.update',$expense) }}" method="POST" class="card p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الفئة</label>
                <select name="category" id="category-select" class="input-field w-full px-3 py-2.5 text-sm">
                    @php
                        $allCategories = \App\Models\ExpenseCategory::getAllCategories();
                        $currentCategory = App\Models\Expense::getReverseCategoryMapping()[$expense->category] ?? $expense->category;
                    @endphp
                    @foreach($allCategories as $cat)
                    <option value="{{ $cat }}" {{ $currentCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                    <option value="__custom__">أخرى (مخصص)...</option>
                </select>
            </div>
            <div id="custom-category-wrapper" class="hidden">
                <label class="block text-slate-400 text-sm mb-1.5">الفئة المخصصة <span class="text-red-400">*</span></label>
                <input type="text" name="custom_category" id="custom-category-input" class="input-field w-full px-3 py-2.5 text-sm" placeholder="اكتب اسم الفئة الجديدة">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">الوصف</label>
                <input type="text" name="description" value="{{ old('description', $expense->description) }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المبلغ</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes', $expense->notes) }}</textarea>
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
    const categorySelect = document.getElementById('category-select');
    const customCategoryWrapper = document.getElementById('custom-category-wrapper');
    const customCategoryInput = document.getElementById('custom-category-input');

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
