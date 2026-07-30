@extends('layouts.app')
@section('title', 'تقرير كشف حساب مورد')
@section('content')

@include('partials.page-header', ['title' => 'تقرير كشف حساب مورد', 'icon' => 'fa-truck'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">المورد</label>
            <select name="supplier_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">جميع الموردين</option>
                @foreach(\App\Models\Supplier::orderBy('name')->get() as $s)
                <option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
            <input type="date" name="from_date" value="{{ request('from_date', today()->startOfMonth()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
            <input type="date" name="to_date" value="{{ request('to_date', today()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fas fa-filter"></i> عرض التقرير</button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fas fa-file-excel"></i> إكسل</button>
        </div>
    </form>
</div>

@if(request('supplier_id') && isset($supplier))

{{-- Supplier Info Banner --}}
<div class="card p-5 mb-5 border border-slate-700/50">
    <div class="flex flex-wrap items-start gap-6">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-truck text-blue-400 text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-white font-bold text-xl">{{ $supplier->name }}</h2>
                <span class="badge {{ $supplier->is_active ? 'badge-green' : 'badge-gray' }}">{{ $supplier->is_active ? 'نشط' : 'موقف' }}</span>
                <span class="badge badge-purple">{{ $supplier->payment_type_label }}</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                @if($supplier->phone)
                <div class="flex items-center gap-2 text-slate-300">
                    <i class="fas fa-phone text-slate-500 text-xs w-4"></i>
                    <span>{{ $supplier->phone }}</span>
                </div>
                @endif
                @if($supplier->address)
                <div class="flex items-center gap-2 text-slate-300">
                    <i class="fas fa-map-marker-alt text-slate-500 text-xs w-4"></i>
                    <span>{{ $supplier->address }}</span>
                </div>
                @endif
                @if(is_array($supplier->materials) && count($supplier->materials))

                <div class="flex items-center gap-2 text-slate-300">
                    <i class="fas fa-boxes text-slate-500 text-xs w-4"></i>
                    <span>{{ implode('، ', $supplier->materials) }}</span>
                </div>
                @endif
                <div class="flex items-center gap-2 text-slate-300">
                    <i class="fas fa-wallet text-slate-500 text-xs w-4"></i>
                    <span>رصيد جاري: {{ number_format($supplier->balance ?? 0, 0) }}</span>
                </div>
            </div>
            @if($supplier->notes)
            <div class="mt-2 text-slate-400 text-xs bg-slate-800/50 rounded-lg px-3 py-2">{{ $supplier->notes }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Summary Cards --}}
@php
    $hasCars = $totalRentalShifts > 0;
    if ($hasCars) {
        $calculatedBalance = $totalRentalShifts - ($totalDeductions + $totalPayments);
    } else {
        $calculatedBalance = $totalPurchases - $totalPayments - $totalDeductions;
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-{{ $hasCars ? '4' : '4' }} gap-5 mb-6">
    @if(!$hasCars)
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي المشتريات</p>
        <p class="text-2xl font-bold text-white">{{ number_format($totalPurchases, 0) }}</p>
    </div>
    @endif
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي المدفوعات</p>
        <p class="text-2xl font-bold text-green-400">{{ number_format($totalPayments, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">الخصومات</p>
        <p class="text-2xl font-bold text-blue-400">{{ number_format($totalDeductions ?? 0, 0) }}</p>
    </div>
    @if($hasCars)
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">ورديات السيارات</p>
        <p class="text-2xl font-bold text-amber-400">{{ number_format($totalRentalShifts ?? 0, 0) }}</p>
    </div>
    @endif
    <div class="stat-card rounded-2xl p-5 border {{ $calculatedBalance > 0 ? 'border-amber-500/30' : ($calculatedBalance < 0 ? 'border-green-500/30' : 'border-slate-700/50') }}">
        <p class="text-slate-400 text-xs mb-1">الرصيد للفترة المحددة</p>
        <p class="text-2xl font-bold {{ $calculatedBalance > 0 ? 'text-amber-400' : ($calculatedBalance < 0 ? 'text-green-400' : 'text-slate-400') }}">
            @if($calculatedBalance > 0)
                دائن (مطلوب له) {{ number_format($calculatedBalance, 0) }}
            @elseif($calculatedBalance < 0)
                مدين (دفعنا زيادة) {{ number_format(abs($calculatedBalance), 0) }}
            @else
                متعادل
            @endif
        </p>
    </div>
</div>

{{-- Transactions Table --}}
<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
        <h3 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-list text-blue-400"></i>
            حركات حساب المورد
        </h3>
        <span class="text-slate-400 text-xs">{{ $transactions->count() }} حركة</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">التاريخ</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">البيان</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">نوع الحركة</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">مدين (دفعنا له)</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">دائن (اشترينا منه)</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">الرصيد التراكمي</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($transactions as $t)
                <tr class="table-row hover:bg-slate-800/30 transition {{ isset($t->tx_type) && $t->tx_type === 'payment' ? 'bg-green-900/5' : '' }} {{ isset($t->tx_type) && $t->tx_type === 'rental_shift' ? 'bg-amber-900/5' : '' }}">
                    <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ \Carbon\Carbon::parse($t->date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-white">
                        {!! $t->description !!}
                        @if(isset($t->tx_type) && $t->tx_type === 'purchase')
                            @if(isset($t->purchase_items) && count($t->purchase_items))
                                <div class="mt-2 text-xs bg-slate-800/60 p-2.5 rounded-lg border border-slate-700/60 space-y-1.5">
                                    <div class="font-semibold text-slate-400 border-b border-slate-700/50 pb-1 flex items-center justify-between">
                                        <span><i class="fas fa-boxes text-amber-400 ml-1"></i>تفاصيل المشتريات:</span>
                                        <span class="text-[11px] text-slate-400">{{ count($t->purchase_items) }} بند</span>
                                    </div>
                                    @foreach($t->purchase_items as $item)
                                        <div class="flex items-center justify-between gap-3 text-slate-300 py-0.5 border-b border-slate-800/40 last:border-0">
                                            <span class="font-medium text-slate-200">
                                                ↳ {{ $item->inventoryItem?->name_ar ?? $item->description }}
                                                @if($item->inventoryItem && $item->description && $item->description !== $item->inventoryItem->name_ar)
                                                    <span class="text-slate-400 text-[11px]">({{ $item->description }})</span>
                                                @endif
                                            </span>
                                            <span class="font-mono text-slate-300 text-[11px]">
                                                {{ number_format($item->quantity, 2) }} {{ $item->unit }}
                                                <span class="text-slate-500">×</span> {{ number_format($item->unit_price, 2) }}
                                                <span class="text-slate-500">=</span> <strong class="text-amber-400">{{ number_format($item->total_price, 2) }}</strong>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(isset($t->invoice_stock) && count($t->invoice_stock))
                                <div class="mt-2 text-xs text-slate-400 bg-slate-800/40 p-2 rounded-lg border border-slate-700/40 space-y-1">
                                    @foreach($t->invoice_stock as $stockIn)
                                        <div>↳ {{ $stockIn->item->name_ar ?? '' }} - {{ number_format($stockIn->quantity, 2) }} {{ $stockIn->item->unit ?? '' }}</div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                        @if(isset($t->shift_details))
                            <div class="text-xs text-slate-400 mt-1">
                                <span>ساعات: {{ $t->shift_details->hours }}</span>
                                @if($t->shift_details->gratuities > 0)
                                    <span class="mr-2">اكراميات: {{ number_format($t->shift_details->gratuities, 0) }}</span>
                                @endif
                                @if($t->shift_details->cards_cost > 0)
                                    <span class="mr-2">كارتات: {{ number_format($t->shift_details->cards_cost, 0) }}</span>
                                @endif
                                @if($t->shift_details->driver_allowance > 0)
                                    <span class="mr-2">معيشة: {{ number_format($t->shift_details->driver_allowance, 0) }}</span>
                                @endif
                                @if($t->shift_details->fuel_cost > 0)
                                    <span class="mr-2">وقود: {{ number_format($t->shift_details->fuel_cost, 0) }}</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if(isset($t->tx_type))
                            @if($t->tx_type === 'purchase')
                                <span class="badge badge-blue">مشتريات</span>
                            @elseif($t->tx_type === 'stock_in')
                                <span class="badge badge-red">وارد مخزون</span>
                            @elseif($t->tx_type === 'payment')
                                <span class="badge badge-green">دفعة</span>
                            @elseif($t->tx_type === 'credit_payment')
                                <span class="badge badge-yellow">سداد آجل</span>
                            @elseif($t->tx_type === 'deduction')
                                <span class="badge badge-red">خصم</span>
                            @elseif($t->tx_type === 'rental_shift')
                                <span class="badge badge-yellow">وردية</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-green-400 font-bold">{{ $t->debit > 0 ? number_format($t->debit, 0) : '-' }}</td>
                    <td class="px-4 py-3 text-center text-red-400">{{ $t->credit > 0 ? number_format($t->credit, 0) : '-' }}</td>
                    <td class="px-4 py-3 text-center font-bold {{ isset($t->running_balance) ? ($t->running_balance > 0 ? 'text-amber-400' : ($t->running_balance < 0 ? 'text-green-400' : 'text-slate-400')) : 'text-slate-600' }}">
                        @if(isset($t->running_balance))
                            {{ $t->running_balance > 0 ? 'له ' : ($t->running_balance < 0 ? 'عليه ' : '') }}{{ number_format(abs($t->running_balance), 0) }}
                        @else
                            <span class="text-slate-500 text-xs">غير محسوب</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">لا توجد حركات في هذه الفترة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- All Suppliers Summary View --}}
@if(!request('supplier_id') && isset($data))
<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-700 flex items-center justify-between">
        <h3 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-list text-blue-400"></i>
            أرصدة جميع الموردين
        </h3>
        <span class="text-slate-400 text-xs">{{ count($data) }} مورد</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">اسم المورد</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">نوع المواد</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">إجمالي المشتريات</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">إجمالي المدفوعات</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">الرصيد الحالي</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">الحالة</th>
                    <th class="px-4 py-3 text-center text-slate-400 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($data as $supplier)
                <tr class="table-row hover:bg-slate-800/30 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/30 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-truck text-blue-400 text-xs"></i>
                            </div>
                            <div>
                                <div class="text-white font-medium">{{ $supplier['supplier']->name }}</div>
                                @if($supplier['supplier']->phone)
                                <div class="text-slate-400 text-xs">{{ $supplier['supplier']->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-300">
                        @if(is_array($supplier['supplier']->materials) && count($supplier['supplier']->materials))
                            <span class="text-xs">{{ implode('، ', array_slice($supplier['supplier']->materials, 0, 2)) }}</span>
                            @if(count($supplier['supplier']->materials) > 2)
                                <span class="text-slate-500 text-xs">+{{ count($supplier['supplier']->materials) - 2 }}</span>
                            @endif
                        @else
                            <span class="text-slate-500 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-slate-300 font-mono">
                        {{ number_format($supplier['total_purchases'] ?? 0, 0) }}
                    </td>
                    <td class="px-4 py-3 text-center text-green-400 font-mono">
                        {{ number_format($supplier['total_payments'] ?? 0, 0) }}
                    </td>
                    <td class="px-4 py-3 text-center font-bold font-mono">
                        @php
                            $balance = $supplier['outstanding'] ?? 0;
                        @endphp
                        <span class="{{ $balance > 0 ? 'text-amber-400' : ($balance < 0 ? 'text-green-400' : 'text-slate-400') }}">
                            @if($balance > 0)
                                له {{ number_format($balance, 0) }}
                            @elseif($balance < 0)
                                عليه {{ number_format(abs($balance), 0) }}
                            @else
                                متعادل
                            @endif
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="badge {{ $supplier['supplier']->is_active ? 'badge-green' : 'badge-gray' }}">
                            {{ $supplier['supplier']->is_active ? 'نشط' : 'موقف' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('reports.supplier-balance', ['supplier_id' => $supplier['supplier']->id, 'from_date' => request('from_date'), 'to_date' => request('to_date')]) }}" 
                           class="text-blue-400 hover:text-blue-300 text-xs">
                            <i class="fas fa-file-invoice"></i> التفاصيل
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                        لا توجد موردين
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot class="bg-slate-800/50 border-t-2 border-slate-700">
                <tr class="font-bold">
                    <td colspan="2" class="px-4 py-3 text-right text-white">الإجمالي</td>
                    <td class="px-4 py-3 text-center text-slate-300 font-mono">
                        {{ number_format(collect($data)->sum('total_purchases'), 0) }}
                    </td>
                    <td class="px-4 py-3 text-center text-green-400 font-mono">
                        {{ number_format(collect($data)->sum('total_payments'), 0) }}
                    </td>
                    <td class="px-4 py-3 text-center font-mono">
                        @php
                            $totalBalance = collect($data)->sum('outstanding');
                        @endphp
                        <span class="{{ $totalBalance > 0 ? 'text-amber-400' : ($totalBalance < 0 ? 'text-green-400' : 'text-slate-400') }}">
                            @if($totalBalance > 0)
                                لهم {{ number_format($totalBalance, 0) }}
                            @elseif($totalBalance < 0)
                                عليهم {{ number_format(abs($totalBalance), 0) }}
                            @else
                                متعادل
                            @endif
                        </span>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection

