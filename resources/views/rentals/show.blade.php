@extends('layouts.app')
@section('title', $rental->equipment_name)
@section('content')

{{-- ── Header ─────────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('rentals.index') }}" class="text-slate-400 hover:text-white text-sm">السيارات المستأجرة</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">{{ $rental->equipment_name }}</span>
            @if($rental->car_number)
                <span class="text-amber-400 text-sm font-mono">({{ $rental->car_number }})</span>
            @endif
        </div>
        <span class="badge badge-{{ ['active'=>'green','expired'=>'gray','cancelled'=>'red'][$rental->status]??'gray' }}">
            {{ ['active'=>'نشط','expired'=>'منتهي','cancelled'=>'ملغي'][$rental->status]??$rental->status }}
        </span>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('rentals.maintenance.create', $rental) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-wrench"></i> صيانة</a>
        <a href="{{ route('rentals.edit',$rental) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
    </div>
</div>

{{-- ── Summary Cards ────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Car Info --}}
    <div class="card p-6 space-y-3 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i class="fas fa-car text-amber-400 ml-2"></i>بيانات السيارة</h3>
        <div class="flex justify-between"><span class="text-slate-400">السواق</span><span class="text-white">{{ $rental->driver_name ?? '-' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-400">رقم السيارة</span><span class="text-white font-mono">{{ $rental->car_number ?? '-' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-400">المؤجِّر</span><span class="text-white">{{ $rental->supplier?->name ?? '-' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-400">نوع الدفع</span><span class="text-white">{{ ['cash'=>'نقدي','credit'=>'آجل','mixed'=>'مختلط'][$rental->payment_type]??'' }}</span></div>
        @if($rental->notes)<div class="text-slate-400 text-xs pt-2 border-t border-slate-700">{{ $rental->notes }}</div>@endif
    </div>

    {{-- Shift Pricing --}}
    <div class="card p-6 space-y-3 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i class="fas fa-money-bill-wave text-green-400 ml-2"></i>تسعيرة الوردية</h3>
        <div class="flex justify-between"><span class="text-slate-400">سعر الساعة</span><span class="text-amber-400 font-bold">{{ number_format($rental->hourly_price ?? 0, 0) }} ج</span></div>
        <div class="flex justify-between"><span class="text-slate-400">معيشة السواق</span><span class="text-amber-400 font-bold">{{ number_format($rental->driver_allowance ?? 0, 0) }} ج</span></div>
        <div class="border-t border-slate-700 pt-3 space-y-1">
            <div class="flex justify-between"><span class="text-slate-400">إجمالي الورديات</span><span class="text-green-400 font-bold">{{ number_format($totalShifts, 0) }} ج</span></div>
            <div class="flex justify-between"><span class="text-slate-400">منها وقود</span><span class="text-orange-400 font-bold">{{ number_format($totalFuel, 0) }} ج</span></div>
            <div class="flex justify-between"><span class="text-slate-400">عدد الورديات</span><span class="text-white font-bold">{{ $shifts->total() }}</span></div>
        </div>
    </div>

    {{-- Maintenance --}}
    <div class="card p-6 space-y-3 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i class="fas fa-wrench text-blue-400 ml-2"></i>تكاليف الصيانة</h3>
        <div class="flex justify-between"><span class="text-slate-400">إجمالي الصيانة</span><span class="text-red-400 font-bold">{{ number_format($totalMaint, 0) }} ج</span></div>
        <div class="flex justify-between"><span class="text-slate-400">مخصوم من الإيجار</span><span class="text-green-400 font-bold">{{ number_format($deducted, 0) }} ج</span></div>
    </div>
</div>

{{-- ── Add Shift Form ───────────────────────────────────────────────────────────── --}}
<div class="card overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-700 bg-slate-800/40">
        <h3 class="text-white font-bold flex items-center gap-2">
            <i class="fas fa-plus-circle text-amber-400"></i> تسجيل وردية جديدة
        </h3>
    </div>
    <div class="p-6">
        <form action="{{ route('rentals.shifts.store', $rental) }}" method="POST" id="shift-form">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">تاريخ الوردية <span class="text-red-400">*</span></label>
                    <input type="date" name="shift_date" value="{{ today()->toDateString() }}" required
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">عدد الساعات <span class="text-red-400">*</span></label>
                    <input type="number" step="0.5" name="hours" id="hours" value="0" required min="0"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">سعر الساعة <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" name="hourly_price" id="hourly_price" value="{{ $rental->hourly_price ?? 0 }}" required min="0"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">تكلفة الساعات</label>
                    <input type="text" id="hours_cost_display" value="0" readonly
                        class="input-field w-full px-3 py-2 text-sm bg-slate-800/60 text-amber-400 font-bold cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">اكراميات</label>
                    <input type="number" step="0.01" name="gratuities" id="gratuities" value="0" min="0"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">كارتات</label>
                    <input type="number" step="0.01" name="cards_cost" id="cards_cost" value="0" min="0"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">معيشة السواق</label>
                    <input type="number" step="0.01" name="driver_allowance" id="driver_allowance" value="{{ $rental->driver_allowance ?? 0 }}" min="0"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">الإجمالي</label>
                    <input type="text" id="total_cost_display" value="0" readonly
                        class="input-field w-full px-3 py-2 text-sm bg-slate-800/60 text-green-400 font-bold text-base cursor-not-allowed">
                </div>
            </div>

            {{-- Gas Section --}}
            <div class="border border-slate-700 rounded-xl p-4 mb-4 bg-slate-800/30">
                <h4 class="text-slate-300 text-sm font-bold mb-3 flex items-center gap-2">
                    <i class="fas fa-gas-pump text-orange-400"></i> وقود (گاز) - اختياري
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-400 text-xs mb-1.5">صنف الوقود من المخزون</label>
                        <select name="fuel_item_id" id="fuel_item_id" class="input-field w-full px-3 py-2 text-sm">
                            <option value="">-- بدون وقود --</option>
                            @foreach($gasItems as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->price_per_unit }}">
                                {{ $item->name_ar }} ({{ number_format($item->current_stock, 1) }} {{ $item->unit }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 text-xs mb-1.5">الكمية (لتر)</label>
                        <input type="number" step="0.001" name="fuel_liters" id="fuel_liters" min="0"
                            class="input-field w-full px-3 py-2 text-sm" placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-slate-400 text-xs mb-1.5">تكلفة الوقود (تلقائي)</label>
                        <input type="text" id="fuel_cost_display" value="0.00" readonly
                            class="input-field w-full px-3 py-2 text-sm bg-slate-800/60 text-orange-400 font-bold cursor-not-allowed">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-slate-400 text-xs mb-1.5">ملاحظات</label>
                    <input type="text" name="notes" class="input-field w-full px-3 py-2 text-sm" placeholder="اختياري">
                </div>
            </div>

            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm">
                <i class="fas fa-plus"></i> إضافة الوردية
            </button>
        </form>
    </div>
</div>

{{-- ── Shifts Table ─────────────────────────────────────────────────────────────── --}}
<div class="card overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h3 class="text-white font-bold flex items-center gap-2">
            <i class="fas fa-list text-amber-400"></i> سجل الورديات
            <span class="badge badge-gray text-xs">{{ $shifts->total() }} وردية</span>
        </h3>
        @if($shifts->total() > 0)
        <span class="text-green-400 font-bold text-sm">الإجمالي: {{ number_format($totalShifts, 0) }} ج</span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-800/50 border-b border-slate-700">
                @foreach(['التاريخ','الساعات','سعر الساعة','تكلفة الساعات','اكراميات','كارتات','معيشة','وقود (ل)','تكلفة الوقود','الإجمالي','حذف'] as $h)
                <th class="px-3 py-3 text-right text-slate-400 font-medium whitespace-nowrap">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($shifts as $shift)
                <tr class="table-row">
                    <td class="px-3 py-3 text-slate-300 whitespace-nowrap">{{ $shift->shift_date->format('d/m/Y') }}</td>
                    <td class="px-3 py-3 text-white">{{ number_format($shift->hours, 1) }}</td>
                    <td class="px-3 py-3 text-slate-300">{{ number_format($shift->hourly_price, 0) }}</td>
                    <td class="px-3 py-3 text-amber-400 font-bold">{{ number_format($shift->hours_cost, 0) }}</td>
                    <td class="px-3 py-3 text-blue-400">{{ $shift->gratuities > 0 ? number_format($shift->gratuities, 0) : '-' }}</td>
                    <td class="px-3 py-3 text-purple-400">{{ $shift->cards_cost > 0 ? number_format($shift->cards_cost, 0) : '-' }}</td>
                    <td class="px-3 py-3 text-cyan-400">{{ $shift->driver_allowance > 0 ? number_format($shift->driver_allowance, 0) : '-' }}</td>
                    <td class="px-3 py-3 text-orange-400">{{ $shift->fuel_liters ? number_format($shift->fuel_liters, 1) : '-' }}</td>
                    <td class="px-3 py-3 text-orange-300">{{ $shift->fuel_cost ? number_format($shift->fuel_cost, 0) : '-' }}</td>
                    <td class="px-3 py-3 text-green-400 font-bold">{{ number_format($shift->total_cost, 0) }}</td>
                    <td class="px-3 py-3">
                        <form action="{{ route('rentals.shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('حذف هذه الوردية وعكس جميع المعاملات المالية؟')">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-300 text-xs px-2 py-1 border border-red-400/30 rounded"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="px-4 py-8 text-center text-slate-500">لا توجد ورديات مسجلة</td></tr>
                @endforelse
            </tbody>
            @if($shifts->total() > 0)
            <tfoot>
                <tr class="bg-slate-800/60 border-t-2 border-slate-600 font-bold">
                    <td class="px-3 py-3 text-slate-300">الإجمالي</td>
                    <td class="px-3 py-3 text-white">{{ number_format($rental->shifts->sum('hours'), 1) }}</td>
                    <td class="px-3 py-3"></td>
                    <td class="px-3 py-3 text-amber-400">{{ number_format($rental->shifts->sum('hours_cost'), 0) }}</td>
                    <td class="px-3 py-3 text-blue-400">{{ number_format($rental->shifts->sum('gratuities'), 0) }}</td>
                    <td class="px-3 py-3 text-purple-400">{{ number_format($rental->shifts->sum('cards_cost'), 0) }}</td>
                    <td class="px-3 py-3 text-cyan-400">{{ number_format($rental->shifts->sum('driver_allowance'), 0) }}</td>
                    <td class="px-3 py-3 text-orange-400">{{ number_format($rental->shifts->sum('fuel_liters'), 1) }}</td>
                    <td class="px-3 py-3 text-orange-300">{{ number_format($rental->shifts->sum('fuel_cost'), 0) }}</td>
                    <td class="px-3 py-3 text-green-400">{{ number_format($totalShifts, 0) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($shifts->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $shifts->links() }}
    </div>
    @endif
</div>

{{-- ── Maintenance Table ────────────────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
        <h3 class="text-white font-bold flex items-center gap-2">
            <i class="fas fa-wrench text-blue-400"></i> سجلات الصيانة
        </h3>
        <a href="{{ route('rentals.maintenance.create',$rental) }}" class="text-amber-400 text-xs hover:text-amber-300">+ إضافة</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-800/50 border-b border-slate-700">
                @foreach(['التاريخ','الوصف','التكلفة','مسجل في الخزينة','ملاحظات'] as $h)
                <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($maintenance as $m)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300 text-sm">{{ $m->maintenance_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-white text-sm">{{ $m->description }}</td>
                    <td class="px-4 py-3 text-red-400 font-bold">{{ number_format($m->cost,0) }} ج</td>
                    <td class="px-4 py-3"><span class="badge badge-green"><i class="fas fa-check ml-1"></i>نعم</span></td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $m->notes }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">لا توجد سجلات صيانة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($maintenance->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $maintenance->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hoursInput     = document.getElementById('hours');
    const hourlyInput    = document.getElementById('hourly_price');
    const gratuities     = document.getElementById('gratuities');
    const cardsCost      = document.getElementById('cards_cost');
    const driverAllow    = document.getElementById('driver_allowance');
    const fuelLiters     = document.getElementById('fuel_liters');
    const fuelItemSelect = document.getElementById('fuel_item_id');

    const hoursCostDisplay = document.getElementById('hours_cost_display');
    const totalDisplay     = document.getElementById('total_cost_display');
    const fuelCostDisplay  = document.getElementById('fuel_cost_display');

    function getVal(el) { return parseFloat(el.value) || 0; }

    function recalc() {
        const hrs    = getVal(hoursInput);
        const hPrice = getVal(hourlyInput);
        const hCost  = hrs * hPrice;
        hoursCostDisplay.value = hCost.toFixed(0);

        // Fuel cost from selected item's price_per_unit
        const selectedOption = fuelItemSelect.options[fuelItemSelect.selectedIndex];
        const pricePerUnit   = parseFloat(selectedOption?.dataset?.price || 0);
        const liters         = getVal(fuelLiters);
        const fCost          = pricePerUnit * liters;
        fuelCostDisplay.value = fCost.toFixed(2);

        const total = hCost + getVal(gratuities) + getVal(cardsCost) + getVal(driverAllow) + fCost;
        totalDisplay.value = total.toFixed(0);
    }

    [hoursInput, hourlyInput, gratuities, cardsCost, driverAllow, fuelLiters, fuelItemSelect]
        .forEach(el => el.addEventListener('input', recalc));

    recalc();
});
</script>
@endpush
@endsection
