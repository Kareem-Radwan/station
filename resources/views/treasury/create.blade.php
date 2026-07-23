@extends('layouts.app')
@section('title', 'قيد خزينة يدوي')
@section('content')

@include('partials.page-header', ['title' => 'تسجيل حركة خزينة يدوية', 'icon' => 'fa-vault'])

{{-- Category definitions: which type (in/out) is suggested, and what reference model to show --}}
@php
$categoryGroups = [
    'incoming' => [
        'label' => 'وارد (إيرادات)',
        'icon'  => 'fa-arrow-down',
        'color' => 'green',
        'items' => [
            'customer_payment'         => ['label' => 'دفعة من عميل',           'ref' => 'customer',     'icon' => 'fa-user'],
            'receipt_in'               => ['label' => 'سند قبض',                'ref' => 'order',        'icon' => 'fa-file-invoice'],
            'contributor_payment'      => ['label' => 'دفعة من مساهم',          'ref' => 'contributor',  'icon' => 'fa-handshake'],
            'inventory_sale'           => ['label' => 'بيع مخزون',              'ref' => null,           'icon' => 'fa-boxes-stacked'],
            'employee_borrow_repayment'=> ['label' => 'سداد سلفة موظف',        'ref' => 'employee',     'icon' => 'fa-user-tie'],
            'employee_borrow_return'   => ['label' => 'إلغاء سلفة موظف',       'ref' => 'employee',     'icon' => 'fa-user-tie'],
            'employee_deductions'      => ['label' => 'خصومات الموظفين',        'ref' => 'employee',     'icon' => 'fa-user-minus'],
        ],
    ],
    'outgoing' => [
        'label' => 'صادر (مصروفات)',
        'icon'  => 'fa-arrow-up',
        'color' => 'red',
        'items' => [
            'supplier_payment'    => ['label' => 'دفعة لمورد',                   'ref' => 'supplier',     'icon' => 'fa-truck'],
            'inventory_purchase'  => ['label' => 'شراء مخزون',                  'ref' => 'supplier',     'icon' => 'fa-cart-shopping'],
            'material_cost'       => ['label' => 'تكلفة المواد',                  'ref' => 'supplier',     'icon' => 'fa-cube'],
            'receipt_out'         => ['label' => 'سند صرف',                     'ref' => 'order',        'icon' => 'fa-file-invoice-dollar'],
            'rental'              => ['label' => 'مصاريف إيجار',                 'ref' => 'rental',       'icon' => 'fa-file-contract'],
            'rental_maintenance'  => ['label' => 'صيانة المعدات المستأجرة',     'ref' => 'rental',       'icon' => 'fa-wrench'],
            'vehicle_equipment'   => ['label' => 'مصاريف مركبات ومعدات',        'ref' => 'equipment',    'icon' => 'fa-truck-monster'],
            'plant_maintenance'   => ['label' => 'صيانة المحطة وقطع الغيار',    'ref' => 'equipment',    'icon' => 'fa-screwdriver-wrench'],
            'salary'              => ['label' => 'الرواتب',                      'ref' => 'employee',     'icon' => 'fa-money-bill-wave'],
            'overtime'            => ['label' => 'العمل الإضافي',               'ref' => 'employee',     'icon' => 'fa-clock'],
            'employee_borrow'     => ['label' => 'سلفة موظف',                   'ref' => 'employee',     'icon' => 'fa-hand-holding-dollar'],
            'credit_payment'      => ['label' => 'سداد ديون',                   'ref' => 'customer',     'icon' => 'fa-credit-card'],
            'land_rent'           => ['label' => 'إيجار الأرض',                 'ref' => 'land_rent',    'icon' => 'fa-map'],
            'expense'             => ['label' => 'مصروفات عامة',                'ref' => null,           'icon' => 'fa-receipt'],
        ],
    ],
];

// Encode data as JSON for JS usage
$customersJson    = $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone])->values();
$suppliersJson    = $suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'phone' => $s->phone])->values();
$employeesJson    = $employees->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'position' => $e->position])->values();
$contributorsJson = $contributors->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values();
$equipmentJson    = $equipment->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'type_label' => $e->type_label])->values();
$rentalsJson      = $rentalContracts->map(fn($r) => ['id' => $r->id, 'name' => $r->equipment_name])->values();
$landRentsJson    = $landRents->map(fn($l) => ['id' => $l->id, 'name' => $l->description . ' (' . number_format($l->annual_amount,2) . ' ج.م)'])->values();
$ordersJson       = $orders->map(fn($o) => ['id' => $o->id, 'name' => 'طلب #' . $o->id . ' - ' . ($o->customer->name ?? '') . ' - ' . ($o->delivery_date ? $o->delivery_date->format('d/m/Y') : '') . ' (' . number_format($o->total_amount,2) . ')'])->values();

// Map category => reference model type
$categoryRefMap = [];
foreach ($categoryGroups as $group) {
    foreach ($group['items'] as $cat => $info) {
        $categoryRefMap[$cat] = $info['ref'];
    }
}
@endphp

<div class="max-w-4xl">
    <form action="{{ route('treasury.store') }}" method="POST" id="treasury-form" class="space-y-6">
        @csrf

        {{-- ─── Section 1: Basic Info ───────────────────────────── --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-circle-info text-amber-400"></i> معلومات أساسية
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Date --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">تاريخ الحركة <span class="text-red-400">*</span></label>
                    <input type="date" name="transaction_date" id="transaction_date"
                           value="{{ old('transaction_date', today()->toDateString()) }}"
                           required class="input-field w-full px-3 py-2.5 text-sm">
                    @error('transaction_date')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Type (in/out) --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">النوع <span class="text-red-400">*</span></label>
                    <select name="type" id="type-select" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="">— اختر —</option>
                        <option value="in"  {{ old('type')=='in'  ? 'selected' : '' }}>⬇ وارد (+)</option>
                        <option value="out" {{ old('type')=='out' ? 'selected' : '' }}>⬆ صادر (−)</option>
                    </select>
                    @error('type')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">المبلغ (ج.م) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                           value="{{ old('amount') }}"
                           required placeholder="0.00" class="input-field w-full px-3 py-2.5 text-sm">
                    @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Payment Method --}}
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5">طريقة الدفع <span class="text-red-400">*</span></label>
                    <select name="payment_method" id="payment_method" required class="input-field w-full px-3 py-2.5 text-sm">
                        <option value="cash"          {{ old('payment_method','cash')=='cash'          ? 'selected' : '' }}>نقدي</option>
                        <option value="bank_transfer" {{ old('payment_method')=='bank_transfer'        ? 'selected' : '' }}>تحويل بنكي</option>
                        <option value="check"         {{ old('payment_method')=='check'               ? 'selected' : '' }}>شيك</option>
                    </select>
                    @error('payment_method')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ─── Section 2: Category Picker ─────────────────────── --}}
        <div class="card p-6" id="category-section">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-tags text-amber-400"></i> الفئة / التصنيف <span class="text-red-400">*</span>
            </h2>

            {{-- Hidden actual category input --}}
            <input type="hidden" name="category" id="category-hidden" value="{{ old('category') }}">
            @error('category')<p class="text-red-400 text-xs mb-3">{{ $message }}</p>@enderror

            {{-- Category tiles - INCOMING --}}
            <div id="group-in" class="hidden mb-2">
                <p class="text-xs text-green-400 font-semibold mb-3 flex items-center gap-1.5">
                    <i class="fas fa-arrow-circle-down"></i> فئات الوارد (إيرادات)
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2" id="tiles-in">
                    @foreach($categoryGroups['incoming']['items'] as $catKey => $catInfo)
                    <button type="button" data-cat="{{ $catKey }}" data-ref="{{ $catInfo['ref'] }}"
                            class="cat-tile text-right px-3 py-3 rounded-xl border border-slate-700 bg-slate-800/50 hover:border-green-500/60 hover:bg-green-900/20 transition-all duration-150 group">
                        <i class="fas {{ $catInfo['icon'] }} text-green-400 text-sm mb-1.5 block"></i>
                        <span class="text-xs text-slate-300 leading-snug group-hover:text-white block">{{ $catInfo['label'] }}</span>
                    </button>
                    @endforeach
                    {{-- Other tile (in) --}}
                    <button type="button" data-cat="other" data-ref="none"
                            class="cat-tile text-right px-3 py-3 rounded-xl border border-dashed border-slate-600 bg-slate-800/30 hover:border-amber-400/60 hover:bg-amber-900/10 transition-all duration-150 group">
                        <i class="fas fa-pen text-amber-400 text-sm mb-1.5 block"></i>
                        <span class="text-xs text-slate-400 leading-snug group-hover:text-white block">أخرى (يدوي)</span>
                    </button>
                </div>
            </div>

            {{-- Category tiles - OUTGOING --}}
            <div id="group-out" class="hidden">
                <p class="text-xs text-red-400 font-semibold mb-3 flex items-center gap-1.5">
                    <i class="fas fa-arrow-circle-up"></i> فئات الصادر (مصروفات)
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2" id="tiles-out">
                    @foreach($categoryGroups['outgoing']['items'] as $catKey => $catInfo)
                    <button type="button" data-cat="{{ $catKey }}" data-ref="{{ $catInfo['ref'] }}"
                            class="cat-tile text-right px-3 py-3 rounded-xl border border-slate-700 bg-slate-800/50 hover:border-red-500/60 hover:bg-red-900/20 transition-all duration-150 group">
                        <i class="fas {{ $catInfo['icon'] }} text-red-400 text-sm mb-1.5 block"></i>
                        <span class="text-xs text-slate-300 leading-snug group-hover:text-white block">{{ $catInfo['label'] }}</span>
                    </button>
                    @endforeach
                    {{-- Other tile (out) --}}
                    <button type="button" data-cat="other" data-ref="none"
                            class="cat-tile text-right px-3 py-3 rounded-xl border border-dashed border-slate-600 bg-slate-800/30 hover:border-amber-400/60 hover:bg-amber-900/10 transition-all duration-150 group">
                        <i class="fas fa-pen text-amber-400 text-sm mb-1.5 block"></i>
                        <span class="text-xs text-slate-400 leading-snug group-hover:text-white block">أخرى (يدوي)</span>
                    </button>
                </div>
            </div>

            {{-- Placeholder before type is selected --}}
            <div id="category-placeholder" class="py-6 text-center text-slate-600 text-sm">
                <i class="fas fa-arrow-up-right-from-square mb-2 block text-xl"></i>
                اختر نوع الحركة أولاً (وارد / صادر) لعرض الفئات المتاحة
            </div>

            {{-- Custom category (when "other" is selected) --}}
            <div id="custom-category-wrap" class="hidden mt-4">
                <label class="block text-slate-400 text-sm mb-1.5">اكتب اسم الفئة <span class="text-red-400">*</span></label>
                <input type="text" name="custom_category" id="custom-category"
                       placeholder="مثال: تسوية بنكية، استرداد ضريبي..."
                       value="{{ old('custom_category') }}"
                       class="input-field w-full px-3 py-2.5 text-sm">
                @error('custom_category')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- ─── Section 3: Related Entity ───────────────────────── --}}
        <div class="card p-6" id="reference-section">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-link text-amber-400"></i> الربط بالجهة المعنية
                <span class="text-amber-500/70 text-xs font-normal">⚠ مهم: حدّد الجهة لكي يظهر القيد في تقارير العميل / المورد / الموظف</span>
            </h2>

            <input type="hidden" name="reference_type" id="reference-type-input" value="{{ old('reference_type') }}">

            {{-- Customer reference --}}
            <div id="ref-customer" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-user text-amber-400 ml-1"></i>العميل</label>
                    <select name="reference_id" id="ref-customer-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر عميلاً —</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('reference_id')==$c->id && old('reference_type')=='customer' ? 'selected' : '' }}>
                            {{ $c->name }} @if($c->phone)— {{ $c->phone }}@endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div id="customer-info" class="hidden bg-slate-800/40 rounded-xl p-3 text-xs text-slate-400 self-end"></div>
            </div>

            {{-- Order reference --}}
            <div id="ref-order" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-file-invoice text-amber-400 ml-1"></i>الطلب / الأمر</label>
                    <select name="reference_id" id="ref-order-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر طلباً —</option>
                        @foreach($orders as $o)
                        <option value="{{ $o->id }}" {{ old('reference_id')==$o->id && old('reference_type')=='order' ? 'selected' : '' }}>
                            طلب #{{ $o->id }} — {{ $o->customer->name ?? '' }} — {{ $o->delivery_date?->format('d/m/Y') }} ({{ number_format($o->total_amount, 2) }} ج.م)
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Supplier reference --}}
            <div id="ref-supplier" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-truck text-amber-400 ml-1"></i>المورد</label>
                    <select name="reference_id" id="ref-supplier-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر موردًا —</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('reference_id')==$s->id && old('reference_type')=='supplier' ? 'selected' : '' }}>
                            {{ $s->name }} @if($s->phone)— {{ $s->phone }}@endif
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Employee reference --}}
            <div id="ref-employee" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-user-tie text-amber-400 ml-1"></i>الموظف</label>
                    <select name="reference_id" id="ref-employee-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر موظفاً —</option>
                        @foreach($employees as $e)
                        <option value="{{ $e->id }}" {{ old('reference_id')==$e->id && old('reference_type')=='employee' ? 'selected' : '' }}>
                            {{ $e->name }} @if($e->position)— {{ $e->position }}@endif
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Contributor reference --}}
            <div id="ref-contributor" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-handshake text-amber-400 ml-1"></i>المساهم</label>
                    <select name="reference_id" id="ref-contributor-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر مساهماً —</option>
                        @foreach($contributors as $c)
                        <option value="{{ $c->id }}" {{ old('reference_id')==$c->id && old('reference_type')=='contributor' ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Equipment reference --}}
            <div id="ref-equipment" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-truck-monster text-amber-400 ml-1"></i>المعدة / المركبة</label>
                    <select name="reference_id" id="ref-equipment-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر معدة —</option>
                        @foreach($equipment as $eq)
                        <option value="{{ $eq->id }}" {{ old('reference_id')==$eq->id && old('reference_type')=='equipment' ? 'selected' : '' }}>
                            {{ $eq->name }} — {{ $eq->type_label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Rental reference --}}
            <div id="ref-rental" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-file-contract text-amber-400 ml-1"></i>عقد الإيجار</label>
                    <select name="reference_id" id="ref-rental-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر عقداً —</option>
                        @foreach($rentalContracts as $r)
                        <option value="{{ $r->id }}" {{ old('reference_id')==$r->id && old('reference_type')=='rental_contract' ? 'selected' : '' }}>
                            {{ $r->equipment_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Land rent reference --}}
            <div id="ref-land_rent" class="ref-panel hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-sm mb-1.5"><i class="fas fa-map text-amber-400 ml-1"></i>بند إيجار الأرض</label>
                    <select name="reference_id" id="ref-land_rent-select" class="input-field w-full px-3 py-2.5 text-sm ref-id-field">
                        <option value="">— اختر بنداً —</option>
                        @foreach($landRents as $l)
                        <option value="{{ $l->id }}" {{ old('reference_id')==$l->id && old('reference_type')=='land_rent' ? 'selected' : '' }}>
                            {{ $l->description }} ({{ number_format($l->annual_amount,2) }} ج.م) — استحقاق: {{ $l->due_date?->format('d/m/Y') }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- No reference (default state) --}}
            <div id="ref-none" class="py-4 text-center text-slate-600 text-xs">
                <i class="fas fa-circle-info mb-1 block text-base"></i>
                سيظهر هنا حقل اختيار الجهة بعد تحديد الفئة
            </div>
        </div>

        {{-- ─── Section 4: Description / Notes ─────────────────── --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
                <i class="fas fa-pen-to-square text-amber-400"></i> البيان والملاحظات
            </h2>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الوصف / البيان <span class="text-red-400">*</span></label>
                <textarea name="description" id="description" rows="3" required
                          placeholder="اكتب وصفاً تفصيلياً للحركة المالية..."
                          class="input-field w-full px-3 py-2.5 text-sm resize-none">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- ─── Summary Preview ──────────────────────────────────── --}}
        <div id="summary-preview" class="hidden card p-5 border border-amber-500/30 bg-amber-900/10">
            <p class="text-xs text-amber-400 font-semibold mb-3 flex items-center gap-1.5">
                <i class="fas fa-eye"></i> معاينة الحركة
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div><span class="text-slate-500 block mb-0.5">التاريخ</span><span id="prev-date" class="text-white font-medium">—</span></div>
                <div><span class="text-slate-500 block mb-0.5">النوع</span><span id="prev-type" class="font-bold">—</span></div>
                <div><span class="text-slate-500 block mb-0.5">الفئة</span><span id="prev-cat" class="text-white">—</span></div>
                <div><span class="text-slate-500 block mb-0.5">المبلغ</span><span id="prev-amount" class="text-white font-bold">—</span></div>
            </div>
        </div>

        {{-- ─── Action Buttons ───────────────────────────────────── --}}
        <div class="flex flex-wrap gap-4 pt-1">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-8 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> حفظ الحركة
            </button>
            <a href="{{ route('treasury.index') }}" class="text-slate-400 hover:text-white text-sm px-6 py-3 rounded-xl border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-xmark"></i> إلغاء
            </a>
        </div>

    </form>
</div>

@push('scripts')
<script>
// ── Data from PHP ──────────────────────────────────────────────────
const categoryRefMap = @json($categoryRefMap);

// Reference type per ref key
const refTypeMap = {
    customer:    'customer',
    order:       'order',
    supplier:    'supplier',
    employee:    'employee',
    contributor: 'contributor',
    equipment:   'equipment',
    rental:      'rental_contract',
    land_rent:   'land_rent',
};

// ── DOM refs ──────────────────────────────────────────────────────
const typeSelect         = document.getElementById('type-select');
const categoryHidden     = document.getElementById('category-hidden');
const refTypeInput       = document.getElementById('reference-type-input');
const groupIn            = document.getElementById('group-in');
const groupOut           = document.getElementById('group-out');
const catPlaceholder     = document.getElementById('category-placeholder');
const customCatWrap      = document.getElementById('custom-category-wrap');
const customCatInput     = document.getElementById('custom-category');
const summaryPreview     = document.getElementById('summary-preview');
const prevDate           = document.getElementById('prev-date');
const prevType           = document.getElementById('prev-type');
const prevCat            = document.getElementById('prev-cat');
const prevAmount         = document.getElementById('prev-amount');
const amountInput        = document.getElementById('amount');
const dateInput          = document.getElementById('transaction_date');
const descInput          = document.getElementById('description');

let selectedCat          = '';
let selectedCatLabel     = '';

// ── Show/hide category groups on type change ──────────────────────
typeSelect.addEventListener('change', function () {
    const val = this.value;
    groupIn.classList.toggle('hidden', val !== 'in');
    groupOut.classList.toggle('hidden', val !== 'out');
    catPlaceholder.classList.toggle('hidden', val !== '');

    // Reset category selection when type changes
    clearCategorySelection();
    updatePreview();
});

// ── Category tile click ───────────────────────────────────────────
document.querySelectorAll('.cat-tile').forEach(btn => {
    btn.addEventListener('click', function () {
        const cat = this.dataset.cat;
        const ref = this.dataset.ref;
        const label = this.querySelector('span').textContent.trim();

        selectCategory(cat, label, ref, this);
    });
});

function selectCategory(cat, label, ref, clickedBtn) {
    // Visual: deselect all
    document.querySelectorAll('.cat-tile').forEach(b => {
        b.classList.remove('ring-2', 'ring-amber-400', 'border-amber-400', 'bg-amber-900/20');
    });
    // Select clicked
    if (clickedBtn) {
        clickedBtn.classList.add('ring-2', 'ring-amber-400', 'border-amber-400', 'bg-amber-900/20');
    }

    selectedCat = cat;
    selectedCatLabel = label;
    categoryHidden.value = cat;

    // Custom category field
    if (cat === 'other') {
        customCatWrap.classList.remove('hidden');
        customCatInput.required = true;
    } else {
        customCatWrap.classList.add('hidden');
        customCatInput.required = false;
    }

    // Show correct reference panel
    showReferencePanel(ref || 'none');

    // Auto-fill description hint
    if (!descInput.value && cat !== 'other') {
        descInput.placeholder = 'اكتب وصفاً تفصيلياً — مثال: ' + label + ' بتاريخ ' + dateInput.value;
    }

    updatePreview();
}

function clearCategorySelection() {
    selectedCat = '';
    selectedCatLabel = '';
    categoryHidden.value = '';
    document.querySelectorAll('.cat-tile').forEach(b => {
        b.classList.remove('ring-2', 'ring-amber-400', 'border-amber-400', 'bg-amber-900/20');
    });
    customCatWrap.classList.add('hidden');
    customCatInput.required = false;
    showReferencePanel('none');
}

// ── Show the right reference panel ───────────────────────────────
function showReferencePanel(refKey) {
    // Hide all
    document.querySelectorAll('.ref-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('ref-none').classList.add('hidden');

    // Clear all ref-id-field values to avoid sending stale values
    document.querySelectorAll('.ref-id-field').forEach(sel => {
        // Reset the name so only active one submits
        sel.removeAttribute('name');
    });

    const panelId = 'ref-' + refKey;
    const panel = document.getElementById(panelId);

    if (panel && refKey && refKey !== 'none') {
        panel.classList.remove('hidden');
        // Restore the name on the active select
        const activeSelect = panel.querySelector('.ref-id-field');
        if (activeSelect) activeSelect.setAttribute('name', 'reference_id');
        // Set the hidden reference_type
        refTypeInput.value = refTypeMap[refKey] || refKey;
    } else {
        document.getElementById('ref-none').classList.remove('hidden');
        refTypeInput.value = '';
    }
}

// ── Live preview ──────────────────────────────────────────────────
function updatePreview() {
    const hasAll = typeSelect.value && categoryHidden.value && amountInput.value;
    summaryPreview.classList.toggle('hidden', !hasAll);

    if (!hasAll) return;

    prevDate.textContent   = dateInput.value || '—';
    prevAmount.textContent = parseFloat(amountInput.value || 0).toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';

    if (typeSelect.value === 'in') {
        prevType.textContent  = '⬇ وارد';
        prevType.className    = 'font-bold text-green-400';
    } else {
        prevType.textContent  = '⬆ صادر';
        prevType.className    = 'font-bold text-red-400';
    }

    const catLabel = categoryHidden.value === 'other'
        ? (customCatInput.value || 'أخرى')
        : selectedCatLabel;
    prevCat.textContent = catLabel;
}

amountInput.addEventListener('input', updatePreview);
dateInput.addEventListener('change', updatePreview);
customCatInput.addEventListener('input', updatePreview);

// ── Restore old state after validation failure ────────────────────
(function restoreOldState() {
    const oldCat  = @json(old('category', ''));
    const oldType = @json(old('type', ''));
    const oldRef  = @json(old('reference_type', ''));

    if (oldType) {
        typeSelect.value = oldType;
        typeSelect.dispatchEvent(new Event('change'));
    }
    if (oldCat) {
        // Find the tile with this category
        const tile = document.querySelector('.cat-tile[data-cat="' + oldCat + '"]');
        const refKey = Object.keys(refTypeMap).find(k => refTypeMap[k] === oldRef) || 'none';
        if (tile) {
            const label = tile.querySelector('span')?.textContent?.trim() || oldCat;
            selectCategory(oldCat, label, tile.dataset.ref, tile);
        } else {
            // Category not found in tiles (custom)
            categoryHidden.value = oldCat;
            showReferencePanel('none');
        }
    }
})();
</script>
@endpush

@endsection
