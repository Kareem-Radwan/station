@extends('layouts.app')
@section('title', 'تعديل حركة الخزينة')
@section('content')

@include('partials.page-header', ['title' => 'تعديل حركة الخزينة', 'icon' => 'fa-edit'])

<div class="max-w-3xl">
    <form action="{{ route('treasury.update', $treasury) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-circle-info text-amber-400"></i> معلومات الحركة
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Date --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ الحركة <span class="text-red-400">*</span></label>
                    <input type="date" name="transaction_date" id="transaction_date"
                           value="{{ old('transaction_date', $treasury->transaction_date->format('Y-m-d')) }}"
                           required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('transaction_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Type (in/out) --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">النوع <span class="text-red-400">*</span></label>
                    <select name="type" id="type-select" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="in"  {{ old('type', $treasury->type)=='in'  ? 'selected' : '' }}>⬇ وارد (+)</option>
                        <option value="out" {{ old('type', $treasury->type)=='out' ? 'selected' : '' }}>⬆ صادر (−)</option>
                    </select>
                    @error('type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">الفئة <span class="text-red-400">*</span></label>
                    <select name="category" id="category-select" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="customer_payment" {{ old('category', $treasury->category)=='customer_payment' ? 'selected' : '' }}>دفعة من عميل</option>
                        <option value="supplier_payment" {{ old('category', $treasury->category)=='supplier_payment' ? 'selected' : '' }}>دفعة لمورد</option>
                        <option value="inventory_purchase" {{ old('category', $treasury->category)=='inventory_purchase' ? 'selected' : '' }}>شراء مخزون</option>
                        <option value="inventory_sale" {{ old('category', $treasury->category)=='inventory_sale' ? 'selected' : '' }}>بيع مخزون</option>
                        <option value="receipt_in" {{ old('category', $treasury->category)=='receipt_in' ? 'selected' : '' }}>سند قبض</option>
                        <option value="receipt_out" {{ old('category', $treasury->category)=='receipt_out' ? 'selected' : '' }}>سند صرف</option>
                        <option value="material_cost" {{ old('category', $treasury->category)=='material_cost' ? 'selected' : '' }}>تكلفة المواد</option>
                        <option value="neighboring_station_outgoing" {{ old('category', $treasury->category)=='neighboring_station_outgoing' ? 'selected' : '' }}>دفعة لمحطة</option>
                        <option value="neighboring_station_incoming" {{ old('category', $treasury->category)=='neighboring_station_incoming' ? 'selected' : '' }}>دفعة من محطة</option>
                        <option value="order_expense" {{ old('category', $treasury->category)=='order_expense' ? 'selected' : '' }}>تكلفة طلب</option>
                        <option value="rental" {{ old('category', $treasury->category)=='rental' ? 'selected' : '' }}>مصاريف إيجار</option>
                        <option value="expense" {{ old('category', $treasury->category)=='expense' ? 'selected' : '' }}>مصروفات عامة</option>
                        <option value="contributor_payment_out" {{ old('category', $treasury->category)=='contributor_payment_out' ? 'selected' : '' }}>دفعة لمساهم</option>
                        <option value="credit_payment" {{ old('category', $treasury->category)=='credit_payment' ? 'selected' : '' }}>سداد ديون</option>
                        <option value="rental_maintenance" {{ old('category', $treasury->category)=='rental_maintenance' ? 'selected' : '' }}>صيانة المعدات المستأجرة</option>
                        <option value="vehicle_equipment" {{ old('category', $treasury->category)=='vehicle_equipment' ? 'selected' : '' }}>مصاريف مركبات ومعدات</option>
                        <option value="plant_maintenance" {{ old('category', $treasury->category)=='plant_maintenance' ? 'selected' : '' }}>صيانة المحطة وقطع الغيار</option>
                        <option value="salary" {{ old('category', $treasury->category)=='salary' ? 'selected' : '' }}>الرواتب</option>
                        <option value="overtime" {{ old('category', $treasury->category)=='overtime' ? 'selected' : '' }}>العمل الإضافي</option>
                        <option value="employee_deductions" {{ old('category', $treasury->category)=='employee_deductions' ? 'selected' : '' }}>خصومات الموظفين</option>
                        <option value="employee_borrow" {{ old('category', $treasury->category)=='employee_borrow' ? 'selected' : '' }}>سلفة موظف</option>
                        <option value="employee_borrow_repayment" {{ old('category', $treasury->category)=='employee_borrow_repayment' ? 'selected' : '' }}>سداد سلفة موظف</option>
                        <option value="contributor_payment" {{ old('category', $treasury->category)=='contributor_payment' ? 'selected' : '' }}>دفعة من مساهم</option>
                        <option value="employee_borrow_return" {{ old('category', $treasury->category)=='employee_borrow_return' ? 'selected' : '' }}>إلغاء سلفة موظف</option>
                        <option value="land_rent" {{ old('category', $treasury->category)=='land_rent' ? 'selected' : '' }}>إيجار الأرض</option>
                    </select>
                    @error('category')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المبلغ (ج.م) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                           value="{{ old('amount', $treasury->amount) }}"
                           required placeholder="0.00" class="input-field w-full px-3 py-2.5 text-sm">
                    @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-pen-to-square text-amber-400"></i> البيان والملاحظات
            </h2>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الوصف / البيان</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="اكتب وصفاً تفصيلياً للحركة المالية..."
                          class="input-field w-full px-3 py-2.5 text-sm resize-none">{{ old('description', $treasury->description) }}</textarea>
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Related Information (Read-Only) --}}
        @if($treasury->reference_type && $treasury->reference_id)
        <div class="card p-6 bg-slate-800/30 border border-slate-700/50">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-link text-amber-400"></i> معلومات الربط
            </h2>
            <div class="text-sm text-slate-400">
                <p><span class="text-slate-500">نوع الربط:</span> <span class="text-white">{{ $treasury->reference_type }}</span></p>
                <p><span class="text-slate-500">معرف الربط:</span> <span class="text-white">{{ $treasury->reference_id }}</span></p>
                <p class="text-amber-400 text-xs mt-2">
                    <i class="fas fa-info-circle"></i> لا يمكن تعديل معلومات الربط. لتغييرها، احذف الحركة وأنشئها من جديد.
                </p>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-4 pt-1">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-8 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> حفظ التعديلات
            </button>
            <a href="{{ route('treasury.index') }}" class="text-slate-400 hover:text-white text-sm px-6 py-3 rounded-xl border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-xmark"></i> إلغاء
            </a>
        </div>

    </form>
</div>

@endsection
