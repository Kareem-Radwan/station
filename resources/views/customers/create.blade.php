@extends('layouts.app')
@section('title', 'إضافة عميل جديد')
@section('content')

@include('partials.page-header', ['title' => 'إضافة عميل جديد', 'icon' => 'fa-user-plus'])

<div class="max-w-3xl">
    <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="card p-6 space-y-5">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                <i class="fas fa-user text-amber-400 text-sm"></i> البيانات الأساسية
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">اسم العميل <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="الاسم الكامل">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رقم الهاتف</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="05xxxxxxxx">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">العنوان / الموقع</label>
                    <input type="text" name="location" value="{{ old('location') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="موقع مشروع البناء">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card p-6 space-y-5">
            <h3 class="text-white font-semibold border-b border-slate-700 pb-3 flex items-center gap-2">
                <i class="fas fa-industry text-amber-400 text-sm"></i> نوع الخرسانة والدفع
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الخرسانة <span class="text-red-400">*</span></label>
                    <select name="concrete_type" id="concreteType" required class="input-field w-full px-3 py-2.5 text-sm" onchange="toggleCementFields()">
                        <option value="">اختر النوع</option>
                        <option value="operational" {{ old('concrete_type')=='operational'?'selected':'' }}>تشغيلية (خصم اسمنت)</option>
                        <option value="complete"    {{ old('concrete_type')=='complete'?'selected':'' }}>متكامل (سعر شامل)</option>
                    </select>
                    @error('concrete_type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">نوع الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_type" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">اختر نوع الدفع</option>
                        <option value="cash"   {{ old('payment_type')=='cash'?'selected':'' }}>نقدي</option>
                        <option value="credit" {{ old('payment_type')=='credit'?'selected':'' }}>آجل</option>
                        <option value="mixed"  {{ old('payment_type')=='mixed'?'selected':'' }}>مختلط</option>
                    </select>
                </div>
            </div>

            {{-- Operational Fields --}}
            <div id="cementFields" class="{{ old('concrete_type') !== 'operational' ? 'hidden' : '' }} grid grid-cols-1 md:grid-cols-3 gap-4 bg-blue-900/20 border border-blue-500/30 rounded-xl p-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">مقاومة الخرسانة</label>
                    <select name="concrete_strength" class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">اختر</option>
                        <option value="180" {{ old('concrete_strength')=='180'?'selected':'' }}>180</option>
                        <option value="250" {{ old('concrete_strength')=='250'?'selected':'' }}>250</option>
                        <option value="300" {{ old('concrete_strength')=='300'?'selected':'' }}>300</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">محتوى الاسمنت (كغ/م³)</label>
                    <input type="number" step="0.001" name="cement_content" value="{{ old('cement_content') }}"
                        class="input-field w-full px-3 py-2.5 text-sm" placeholder="مثال: 350">
                </div>
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">رصيد الاسمنت الأولي (طن)</label>
                    <input type="number" step="0.001" name="cement_balance" value="{{ old('cement_balance', 0) }}"
                        class="input-field w-full px-3 py-2.5 text-sm">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> حفظ العميل
            </button>
            <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 hover:border-slate-500 transition">
                إلغاء
            </a>
        </div>

    </form>
</div>

@push('scripts')
<script>
function toggleCementFields() {
    const type   = document.getElementById('concreteType').value;
    const fields = document.getElementById('cementFields');
    fields.classList.toggle('hidden', type !== 'operational');
}
</script>
@endpush
@endsection
