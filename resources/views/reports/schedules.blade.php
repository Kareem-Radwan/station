@extends('layouts.app')
@section('title', 'تقرير الجداول الأسبوعية')
@section('content')

@include('partials.page-header', ['title' => 'تقرير الجداول الأسبوعية', 'icon' => 'fa-calendar-week'])

{{-- Filter Form --}}
<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">العميل</label>
            <select name="customer_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">حالة الإدخال</label>
            <select name="entry_status" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="pending" {{ request('entry_status') == 'pending' ? 'selected' : '' }}>معلق</option>
                <option value="completed" {{ request('entry_status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                <option value="cancelled" {{ request('entry_status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full">
                <i class="fas fa-filter"></i> تصفية
            </button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-3 py-2 rounded-lg text-sm whitespace-nowrap">
                <i class="fas fa-file-excel"></i> إكسل
            </button>
        </div>
    </form>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card rounded-2xl p-5 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إجمالي إدخالات الجدول</p>
        <p class="text-2xl font-bold text-white">{{ $totals['total_entries'] }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-blue-500/30">
        <p class="text-slate-400 text-xs mb-1">إجمالي الكمية المجدولة</p>
        <p class="text-2xl font-bold text-blue-400">{{ number_format($totals['total_m3'], 2) }} م³</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-green-500/30">
        <p class="text-slate-400 text-xs mb-1">مكتمل</p>
        <p class="text-2xl font-bold text-green-400">{{ $totals['completed'] }}</p>
    </div>
    <div class="stat-card rounded-2xl p-5 border border-yellow-500/30">
        <p class="text-slate-400 text-xs mb-1">معلق / قيد التنفيذ</p>
        <p class="text-2xl font-bold text-yellow-400">{{ $totals['pending'] }}</p>
    </div>
</div>

@if(empty($schedules))
<div class="card p-8 text-center">
    <i class="fas fa-calendar-times text-slate-600 text-4xl mb-3"></i>
    <p class="text-slate-400">لا توجد جداول في الفترة المحددة</p>
</div>
@else

{{-- Schedules --}}
@foreach($schedules as $schedule)
<div class="card mb-6 overflow-hidden">
    {{-- Schedule Header --}}
    <div class="px-5 py-4 border-b border-slate-700 bg-slate-800/30 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center">
                <i class="fas fa-calendar-week text-amber-400"></i>
            </div>
            <div>
                <h3 class="text-white font-bold">
                    الأسبوع {{ $schedule->week_number }} — {{ $schedule->year }}
                </h3>
                <p class="text-slate-400 text-xs mt-0.5">
                    {{ $schedule->week_start->format('d/m/Y') }} → {{ $schedule->week_end->format('d/m/Y') }}
                    &nbsp;|&nbsp; <span class="text-slate-300">{{ $schedule->duration_string ?? $schedule->week_start->diffInDays($schedule->week_end) + 1 }} يوم</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="badge {{ $schedule->status === 'published' ? 'badge-green' : ($schedule->status === 'draft' ? 'badge-yellow' : 'badge-gray') }}">
                {{ $schedule->status_label }}
            </span>
            @if($schedule->createdBy)
            <span class="text-slate-400 text-xs"><i class="fas fa-user ml-1"></i>{{ $schedule->createdBy->name }}</span>
            @endif
            <span class="text-slate-400 text-xs"><i class="fas fa-list ml-1"></i>{{ $schedule->entries->count() }} إدخال</span>
            <span class="text-blue-400 text-xs font-bold">{{ number_format($schedule->entries->sum('quantity_m3'), 2) }} م³</span>
        </div>
    </div>

    @if($schedule->notes)
    <div class="px-5 py-2 bg-slate-800/20 border-b border-slate-700/50">
        <p class="text-slate-400 text-xs"><i class="fas fa-sticky-note ml-1"></i>{{ $schedule->notes }}</p>
    </div>
    @endif

    {{-- Entries Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700 text-xs">
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">رقم الطلب</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">العميل</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">موقع التوصيل</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">تاريخ التوصيل</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">وقت التوصيل</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">الكمية م³</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">نوع الخرسانة</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">الخلطة</th>
                    <th class="px-3 py-3 text-center text-slate-400 font-medium">الحالة</th>
                    <th class="px-3 py-3 text-right text-slate-400 font-medium">ملاحظات المهندس</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($schedule->entries as $entry)
                <tr class="hover:bg-slate-800/30 transition {{ $entry->status === 'completed' ? 'bg-green-900/5' : ($entry->status === 'cancelled' ? 'bg-red-900/5 opacity-60' : '') }}">
                    <td class="px-3 py-3">
                        @if($entry->order_id)
                            <a href="{{ route('orders.show', $entry->order_id) }}" class="text-blue-400 hover:text-blue-300 font-mono">#{{ $entry->order_id }}</a>
                        @else
                            <span class="text-slate-600">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-white font-medium">{{ $entry->customer->name ?? '-' }}</div>
                        @if($entry->customer?->phone)
                        <div class="text-slate-500 text-xs">{{ $entry->customer->phone }}</div>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-slate-300">{{ $entry->site_location ?? '-' }}</td>
                    <td class="px-3 py-3 text-center text-slate-300 whitespace-nowrap">{{ $entry->delivery_date->format('d/m/Y') }}</td>
                    <td class="px-3 py-3 text-center text-amber-400">
                        {{ $entry->delivery_time ? \Carbon\Carbon::parse($entry->delivery_time)->format('H:i') : '-' }}
                    </td>
                    <td class="px-3 py-3 text-center text-blue-400 font-bold">{{ number_format($entry->quantity_m3, 2) }}</td>
                    <td class="px-3 py-3 text-center">
                        @if($entry->order)
                        <span class="badge {{ $entry->order->concrete_type === 'operational' ? 'badge-blue' : 'badge-purple' }} text-xs">
                            {{ $entry->order->concrete_type_label }}
                        </span>
                        @else <span class="text-slate-600">—</span> @endif
                    </td>
                    <td class="px-3 py-3 text-center text-slate-400 text-xs">
                        {{ $entry->order?->concreteMix?->name ?? '-' }}
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="badge {{ $entry->status === 'completed' ? 'badge-green' : ($entry->status === 'cancelled' ? 'badge-red' : 'badge-yellow') }}">
                            {{ $entry->status_label }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-slate-400 text-xs max-w-[150px] truncate" title="{{ $entry->engineer_notes ?? '' }}">
                        {{ $entry->engineer_notes ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-4 py-8 text-center text-slate-500">لا توجد إدخالات</td></tr>
                @endforelse
            </tbody>
            @if($schedule->entries->count() > 0)
            <tfoot>
                <tr class="bg-slate-800/40 border-t border-slate-700 font-bold text-xs">
                    <td colspan="5" class="px-3 py-2 text-white">إجمالي الأسبوع</td>
                    <td class="px-3 py-2 text-center text-blue-400">{{ number_format($schedule->entries->sum('quantity_m3'), 2) }}</td>
                    <td colspan="4" class="px-3 py-2 text-center text-slate-400">
                        <span class="text-green-400 ml-3">✓ {{ $schedule->entries->where('status', 'completed')->count() }} مكتمل</span>
                        <span class="text-yellow-400 ml-3">⏳ {{ $schedule->entries->where('status', 'pending')->count() }} معلق</span>
                        <span class="text-red-400">✗ {{ $schedule->entries->where('status', 'cancelled')->count() }} ملغي</span>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endforeach

@endif

@endsection

