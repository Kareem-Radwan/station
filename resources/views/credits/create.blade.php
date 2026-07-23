@extends('layouts.app')
@section('title', 'إضافة دين / آجل')
@section('content')

@include('partials.page-header', ['title' => 'إضافة دين أو استحقاق جديد', 'icon' => 'fa-plus-circle'])

<div class="max-w-2xl">
    <form action="{{ route('credits.store') }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">النوع <span class="text-red-400">*</span></label>
                <select name="creditable_type" id="creditable_type" class="input-field w-full px-3 py-2.5 text-sm" required>
                    <option value="customer" {{ old('creditable_type', 'customer')=='customer'?'selected':'' }}>عميل</option>
                    <option value="supplier" {{ old('creditable_type')=='supplier'?'selected':'' }}>مورد</option>
                </select>
            </div>
            
            <div id="customer_container">
                <label class="block text-slate-400 text-sm mb-1.5">العميل <span class="text-red-400">*</span></label>
                <select name="creditable_id" id="customer_select" class="input-field w-full px-3 py-2.5 text-sm">
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('creditable_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="supplier_container" style="display: none;">
                <label class="block text-slate-400 text-sm mb-1.5">المورد <span class="text-red-400">*</span></label>
                <select name="creditable_id" id="supplier_select" class="input-field w-full px-3 py-2.5 text-sm" disabled>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ old('creditable_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-400 text-sm mb-1.5">المبلغ <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required min="0.01" class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تاريخ الاستحقاق <span class="text-red-400">*</span></label>
                <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">السبب / الوصف (ملاحظات)</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
            </div>
        </div>
        
        <div class="bg-blue-900/20 border border-blue-500/30 rounded-xl p-3 text-xs text-slate-300">
            <i class="fas fa-info-circle text-blue-400 ml-1"></i>
            الديون الناتجة عن مبيعات الخرسانة الآجلة ومشتريات المواد يتم إنشاؤها تلقائياً مع الطلب أو الفاتورة.
        </div>
        
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ الدين</button>
            <a href="{{ route('credits.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('creditable_type');
    const customerContainer = document.getElementById('customer_container');
    const supplierContainer = document.getElementById('supplier_container');
    const customerSelect = document.getElementById('customer_select');
    const supplierSelect = document.getElementById('supplier_select');

    function toggleType() {
        if (typeSelect.value === 'customer') {
            customerContainer.style.display = 'block';
            supplierContainer.style.display = 'none';
            customerSelect.removeAttribute('disabled');
            supplierSelect.setAttribute('disabled', 'disabled');
        } else {
            customerContainer.style.display = 'none';
            supplierContainer.style.display = 'block';
            customerSelect.setAttribute('disabled', 'disabled');
            supplierSelect.removeAttribute('disabled');
        }
    }

    typeSelect.addEventListener('change', toggleType);
    
    // Run on initial load
    toggleType();
});
</script>
@endsection
