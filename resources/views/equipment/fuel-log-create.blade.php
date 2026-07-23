@extends('layouts.app')
@section('title', 'تسجيل وقود')
@section('content')

@include('partials.page-header', ['title' => 'تسجيل وقود: '.$equipment->name, 'icon' => 'fa-gas-pump'])

<div class="max-w-xl">
    <form action="{{ route('equipment.fuel-logs.store', $equipment) }}" method="POST" class="card p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">التاريخ <span class="text-red-400">*</span></label>
                <input type="date" name="log_date" value="{{ today()->toDateString() }}" required class="input-field w-full px-3 py-2.5 text-sm">
            </div>
            @if($equipment->tracking_type === 'hours')
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تسجيل ساعات <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="hours_logged" min="{{ $equipment->current_hours + 0.01 }}" required
                    class="input-field w-full px-3 py-2.5 text-sm" placeholder="القيمة الجديدة">
                <p class="text-slate-500 text-xs mt-1">الحالية: {{ number_format($equipment->current_hours, 1) }} ساعة</p>
                @error('hours_logged')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @else
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">تسجيل أيام <span class="text-red-400">*</span></label>
                <input type="number" step="1" name="days_logged" min="{{ $equipment->current_days + 1 }}" required
                    class="input-field w-full px-3 py-2.5 text-sm" placeholder="القيمة الجديدة">
                <p class="text-slate-500 text-xs mt-1">الحالية: {{ number_format($equipment->current_days, 0) }} يوم</p>
                @error('days_logged')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الكمية (لتر) <span class="text-red-400">*</span></label>
                <input type="number" step="0.01" name="liters" min="0.01" required
                    class="input-field w-full px-3 py-2.5 text-sm liters-input" oninput="calcFuel()">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">سعر اللتر <span class="text-red-400">*</span></label>
                <input type="number" step="0.001" name="unit_cost" min="0" required id="unitCostInput"
                    class="input-field w-full px-3 py-2.5 text-sm price-input" oninput="calcFuel()">
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الإجمالي</label>
                <div class="input-field px-3 py-2.5 text-amber-400 font-bold" id="fuelTotal">0.00</div>
            </div>
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="deduct_from_inventory" value="1" id="deductCheckbox" 
                        onchange="toggleInventoryDropdown()" class="rounded border-slate-600 bg-slate-800 text-accent-500 focus:ring-accent-500">
                    <span class="text-slate-400 text-sm">خصم الوقود من المخزون</span>
                </label>
            </div>
            <div class="md:col-span-2 hidden" id="inventoryDropdownContainer">
                <label class="block text-slate-400 text-sm mb-1.5">اختر المادة من المخزون <span class="text-red-400">*</span></label>
                <select name="inventory_item_id" id="inventoryItemSelect" onchange="updateInventoryPrice()" class="input-field w-full px-3 py-2.5 text-sm">
                    <option value="">-- اختر المادة --</option>
                    @foreach($inventoryItems as $item)
                        <option value="{{ $item->id }}" data-price="{{ $item->price_per_unit }}">
                            {{ $item->name_ar }} ({{ $item->current_stock }} {{ $item->unit }}) - {{ number_format($item->price_per_unit, 2) }} جنية/{{ $item->unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-slate-400 text-sm mb-1.5">ملاحظات</label>
                <textarea name="notes" rows="2" class="input-field w-full px-3 py-2.5 text-sm"></textarea>
            </div>
        </div>
        <div class="flex gap-4 pt-2">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-save"></i> حفظ</button>
            <a href="{{ route('equipment.show',$equipment) }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function calcFuel() {
    const liters = parseFloat(document.querySelector('.liters-input').value) || 0;
    const price  = parseFloat(document.getElementById('unitCostInput').value) || 0;
    document.getElementById('fuelTotal').textContent = (liters * price).toFixed(2);
}

function toggleInventoryDropdown() {
    const checkbox = document.getElementById('deductCheckbox');
    const container = document.getElementById('inventoryDropdownContainer');
    const select = document.getElementById('inventoryItemSelect');
    const unitCostInput = document.getElementById('unitCostInput');
    
    if (checkbox.checked) {
        container.classList.remove('hidden');
        select.setAttribute('required', 'required');
        updateInventoryPrice();
    } else {
        container.classList.add('hidden');
        select.removeAttribute('required');
        select.value = '';
        // Reset price field to editable when unchecked
        unitCostInput.value = '';
        unitCostInput.removeAttribute('readonly');
        unitCostInput.classList.remove('bg-slate-800/50');
        calcFuel();
    }
}

function updateInventoryPrice() {
    const select = document.getElementById('inventoryItemSelect');
    const liters = parseFloat(document.querySelector('.liters-input').value) || 0;
    const unitCostInput = document.getElementById('unitCostInput');
    
    if (select.value) {
        const selectedOption = select.options[select.selectedIndex];
        const pricePerUnit = parseFloat(selectedOption.dataset.price) || 0;
        
        // Auto-fill the unit cost from inventory
        unitCostInput.value = pricePerUnit.toFixed(3);
        unitCostInput.setAttribute('readonly', 'readonly');
        unitCostInput.classList.add('bg-slate-800/50');
        
        // Recalculate total
        calcFuel();
    } else {
        unitCostInput.value = '';
        unitCostInput.removeAttribute('readonly');
        unitCostInput.classList.remove('bg-slate-800/50');
        calcFuel();
    }
}
</script>
@endpush
@endsection
