@extends('layouts.app')
@section('title', 'تقرير ورديات السيارات')
@section('content')

@include('partials.page-header', ['title' => 'تقرير ورديات السيارات المستأجرة', 'icon' => 'fa-car'])

<div class="card p-6 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-sm mb-1.5">من تاريخ</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="input-field px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-sm mb-1.5">إلى تاريخ</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="input-field px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-accent text-slate-900 font-bold px-5 py-2 rounded-lg text-sm">
            <i class="fas fa-filter ml-1"></i> تصفية
        </button>
        <button type="submit" name="export" value="excel" class="bg-blue-600 hover:bg-green-700 text-white font-bold px-5 py-2 rounded-lg text-sm transition">
            <i class="fas fa-file-excel ml-1"></i> تصدير Excel
        </button>
    </form>
</div>

@if(empty($contractsData))
<div class="card p-8 text-center">
    <i class="fas fa-info-circle text-slate-600 text-4xl mb-3"></i>
    <p class="text-slate-400">لا توجد ورديات في الفترة المحددة</p>
</div>
@else

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">إجمالي الساعات</div>
        <div class="text-white text-xl font-bold">{{ number_format($grandTotals['hours'], 2) }}</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">تكلفة الساعات</div>
        <div class="text-amber-400 text-xl font-bold">{{ number_format($grandTotals['hours_cost'], 0) }} ج</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">الوقود</div>
        <div class="text-orange-400 text-xl font-bold">{{ number_format($grandTotals['fuel_cost'], 0) }} ج</div>
    </div>
    <div class="card p-4">
        <div class="text-slate-400 text-xs mb-1">الإجمالي الكلي</div>
        <div class="text-green-400 text-xl font-bold">{{ number_format($grandTotals['total_cost'], 0) }} ج</div>
    </div>
</div>

{{-- Detailed Data per Car --}}
@foreach($contractsData as $data)
<div class="card p-6 mb-6">
    {{-- Contract Header --}}
    <div class="border-b border-slate-700 pb-4 mb-4">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <i class="fas fa-car text-amber-400"></i>
                    {{ $data['contract']->equipment_name }}
                    @if($data['contract']->car_number)
                        <span class="text-slate-400 text-sm font-normal">({{ $data['contract']->car_number }})</span>
                    @endif
                </h3>
                <p class="text-slate-400 text-sm mt-1">
                    <i class="fas fa-building ml-1"></i>
                    {{ $data['contract']->supplier->name ?? 'غير محدد' }}
                </p>
            </div>
            <div class="text-left">
                <div class="text-slate-400 text-xs">عدد الورديات</div>
                <div class="text-white text-2xl font-bold">{{ $data['shifts']->count() }}</div>
            </div>
        </div>

        {{-- Contract Details Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-4 text-xs">
            @if($data['contract']->driver_name)
            <div class="bg-slate-800/40 rounded-lg p-2 text-center">
                <div class="text-slate-500 mb-1">السائق</div>
                <div class="text-white font-medium">{{ $data['contract']->driver_name }}</div>
            </div>
            @endif
            @if($data['contract']->hourly_price)
            <div class="bg-slate-800/40 rounded-lg p-2 text-center">
                <div class="text-slate-500 mb-1">سعر الساعة</div>
                <div class="text-amber-400 font-bold">{{ number_format($data['contract']->hourly_price, 0) }} ج</div>
            </div>
            @endif
            @if($data['contract']->driver_allowance)
            <div class="bg-slate-800/40 rounded-lg p-2 text-center">
                <div class="text-slate-500 mb-1">بدل معيشة يومي</div>
                <div class="text-purple-400 font-bold">{{ number_format($data['contract']->driver_allowance, 0) }} ج</div>
            </div>
            @endif
            @if($data['contract']->start_date)
            <div class="bg-slate-800/40 rounded-lg p-2 text-center">
                <div class="text-slate-500 mb-1">تاريخ البدء</div>
                <div class="text-slate-300">{{ $data['contract']->start_date->format('Y-m-d') }}</div>
            </div>
            @endif
            @if($data['contract']->end_date)
            <div class="bg-slate-800/40 rounded-lg p-2 text-center">
                <div class="text-slate-500 mb-1">تاريخ الانتهاء</div>
                <div class="text-slate-300">{{ $data['contract']->end_date->format('Y-m-d') }}</div>
            </div>
            @endif
            <div class="bg-slate-800/40 rounded-lg p-2 text-center">
                <div class="text-slate-500 mb-1">حالة العقد</div>
                <span class="badge {{ $data['contract']->status === 'active' ? 'badge-green' : ($data['contract']->status === 'expired' ? 'badge-yellow' : 'badge-red') }}">
                    {{ $data['contract']->status_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- Shifts Table --}}
    <div class="overflow-x-auto mb-4">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700 text-xs">
                    <th class="text-right pb-3 text-slate-400 font-normal">التاريخ</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">الساعات</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">سعر الساعة</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">تكلفة الساعات</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">اكراميات</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">كارتات</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">معيشة السواق</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">الوقود</th>
                    <th class="text-center pb-3 text-slate-400 font-normal">الإجمالي</th>
                    <th class="text-right pb-3 text-slate-400 font-normal">ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['shifts'] as $shift)
                <tr class="border-b border-slate-800 hover:bg-slate-800/30 transition">
                    <td class="py-3 whitespace-nowrap">{{ $shift->shift_date->format('Y-m-d') }}</td>
                    <td class="text-center text-white font-bold">{{ number_format($shift->hours, 2) }}</td>
                    <td class="text-center text-slate-400">{{ $data['contract']->hourly_price ? number_format($data['contract']->hourly_price, 0) : '-' }}</td>
                    <td class="text-center text-amber-400">{{ number_format($shift->hours_cost, 0) }}</td>
                    <td class="text-center text-green-400">{{ number_format($shift->gratuities, 0) }}</td>
                    <td class="text-center text-blue-400">{{ number_format($shift->cards_cost, 0) }}</td>
                    <td class="text-center text-purple-400">{{ number_format($shift->driver_allowance, 0) }}</td>
                    <td class="text-center text-orange-400">{{ number_format($shift->fuel_cost ?? 0, 0) }}</td>
                    <td class="text-center font-bold text-white">{{ number_format($shift->total_cost, 0) }}</td>
                    <td class="text-slate-400 text-xs max-w-[120px] truncate" title="{{ $shift->notes ?? '' }}">{{ $shift->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-slate-700 font-bold text-xs bg-slate-800/30">
                    <td class="py-2 text-white">الإجمالي</td>
                    <td class="text-center text-white">{{ number_format($data['totals']['hours'], 2) }}</td>
                    <td class="text-center text-slate-500">—</td>
                    <td class="text-center text-amber-400">{{ number_format($data['totals']['hours_cost'], 0) }}</td>
                    <td class="text-center text-green-400">{{ number_format($data['totals']['gratuities'], 0) }}</td>
                    <td class="text-center text-blue-400">{{ number_format($data['totals']['cards_cost'], 0) }}</td>
                    <td class="text-center text-purple-400">{{ number_format($data['totals']['driver_allowance'], 0) }}</td>
                    <td class="text-center text-orange-400">{{ number_format($data['totals']['fuel_cost'], 0) }}</td>
                    <td class="text-center font-bold text-green-400 text-sm">{{ number_format($data['totals']['total_cost'], 0) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endforeach

{{-- Grand Total Summary --}}
<div class="card p-6 bg-gradient-to-br from-slate-800 to-slate-900 border-amber-500/30">
    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
        <i class="fas fa-calculator text-amber-400"></i>
        الإجمالي الكلي لجميع السيارات
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-3 bg-slate-900/50 rounded-lg">
            <div class="text-slate-400 text-xs mb-1">إجمالي الساعات</div>
            <div class="text-white text-xl font-bold">{{ number_format($grandTotals['hours'], 2) }}</div>
        </div>
        <div class="text-center p-3 bg-slate-900/50 rounded-lg">
            <div class="text-slate-400 text-xs mb-1">تكلفة الساعات</div>
            <div class="text-amber-400 text-xl font-bold">{{ number_format($grandTotals['hours_cost'], 0) }} ج</div>
        </div>
        <div class="text-center p-3 bg-slate-900/50 rounded-lg">
            <div class="text-slate-400 text-xs mb-1">اكراميات</div>
            <div class="text-green-400 text-xl font-bold">{{ number_format($grandTotals['gratuities'], 0) }} ج</div>
        </div>
        <div class="text-center p-3 bg-slate-900/50 rounded-lg">
            <div class="text-slate-400 text-xs mb-1">كارتات</div>
            <div class="text-blue-400 text-xl font-bold">{{ number_format($grandTotals['cards_cost'], 0) }} ج</div>
        </div>
        <div class="text-center p-3 bg-slate-900/50 rounded-lg">
            <div class="text-slate-400 text-xs mb-1">معيشة السواق</div>
            <div class="text-purple-400 text-xl font-bold">{{ number_format($grandTotals['driver_allowance'], 0) }} ج</div>
        </div>
        <div class="text-center p-3 bg-slate-900/50 rounded-lg">
            <div class="text-slate-400 text-xs mb-1">الوقود</div>
            <div class="text-orange-400 text-xl font-bold">{{ number_format($grandTotals['fuel_cost'], 0) }} ج</div>
        </div>
        <div class="text-center p-3 bg-amber-500/10 rounded-lg border border-amber-500/30 md:col-span-2">
            <div class="text-amber-300 text-xs mb-1">الإجمالي الكلي</div>
            <div class="text-amber-400 text-3xl font-bold">{{ number_format($grandTotals['total_cost'], 0) }} ج</div>
        </div>
    </div>
</div>

@endif

@endsection

