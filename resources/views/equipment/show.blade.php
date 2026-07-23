@extends('layouts.app')
@section('title', $equipment->name)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('equipment.index') }}" class="text-slate-400 hover:text-white text-sm">المعدات</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">{{ $equipment->name }}</span>
        </div>
        <span class="badge badge-{{ ['active'=>'green','maintenance'=>'yellow','inactive'=>'gray'][$equipment->status]??'gray' }}">{{ $equipment->status_label }}</span>
        <span class="badge badge-blue mr-2">{{ $equipment->type_label }}</span>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('equipment.fuel-logs.create', $equipment) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-gas-pump text-orange-400"></i> تسجيل وقود
        </a>
        <a href="{{ route('equipment.maintenance.create', $equipment) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-wrench text-blue-400"></i> تسجيل صيانة
        </a>
        <a href="{{ route('equipment.edit', $equipment) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i></a>
    </div>
</div>

{{-- Cost Summary & Tracking --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-400 to-orange-600"></div>
        <p class="text-slate-400 text-xs mb-1">إجمالي الوقود</p>
        <p class="text-2xl font-bold text-orange-400">{{ number_format($totalFuel, 0) }}</p>
        <p class="text-slate-500 text-xs mt-1">جنية</p>
    </div>
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-400 to-red-600"></div>
        <p class="text-slate-400 text-xs mb-1">إجمالي الصيانة</p>
        <p class="text-2xl font-bold text-red-400">{{ number_format($totalMaint, 0) }}</p>
        <p class="text-slate-500 text-xs mt-1">جنية</p>
    </div>
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-amber-400 to-amber-600"></div>
        <p class="text-slate-400 text-xs mb-1">إجمالي التكاليف</p>
        <p class="text-2xl font-bold text-amber-400">{{ number_format($totalFuel + $totalMaint, 0) }}</p>
        <p class="text-slate-500 text-xs mt-1">جنية</p>
    </div>
    @if($equipment->maintenance_threshold)
    <div class="stat-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-400 to-purple-600"></div>
        <p class="text-slate-400 text-xs mb-1">{{ $equipment->tracking_type_label }}</p>
        <p class="text-2xl font-bold text-purple-400">{{ number_format($equipment->getCurrentValue(), $equipment->tracking_type === 'hours' ? 1 : 0) }}</p>
        <p class="text-slate-500 text-xs mt-1">القادمة: {{ number_format($equipment->getNextMaintenanceValue(), $equipment->tracking_type === 'hours' ? 1 : 0) }}</p>
    </div>
    @endif
</div>

{{-- Maintenance Alert --}}
@if($equipment->needsMaintenance())
<div class="mb-6 bg-gradient-to-r from-red-500/20 to-orange-500/20 border border-red-500/50 rounded-xl p-5">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-red-400 font-bold text-lg mb-2">تنبيه صيانة مطلوبة!</h3>
            <p class="text-slate-300 text-sm mb-3">
                المعدة وصلت لحد الصيانة. القيمة الحالية: 
                <span class="font-bold text-white">{{ number_format($equipment->getCurrentValue(), $equipment->tracking_type === 'hours' ? 1 : 0) }}</span>
                {{ $equipment->tracking_type === 'hours' ? 'ساعة' : 'يوم' }}
            </p>
            <a href="{{ route('equipment.maintenance.create', $equipment) }}" class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-wrench"></i>
                تسجيل صيانة الآن
            </a>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Fuel Logs --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-bold flex items-center gap-2"><i class="fas fa-gas-pump text-orange-400"></i> سجلات الوقود</h3>
            <a href="{{ route('equipment.fuel-logs.create', $equipment) }}" class="text-amber-400 text-xs hover:text-amber-300">+ إضافة</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700">
                        @foreach([
                            'التاريخ',
                            $equipment->tracking_type_label,
                            'اللترات',
                            'سعر الوحدة',
                            'الإجمالي',
                            'المصدر'
                        ] as $h)
                            <th class="px-4 py-2 text-right text-slate-400 font-medium text-xs">
                                {{ $h }}
                            </th>
                        @endforeach
                    </tr>
            </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($fuelLogs as $f)
                    <tr class="table-row">
                        <td class="px-4 py-2 text-slate-300 text-xs">
                            <div>{{ $f->log_date->format('d/m/Y') }}</div>
                            <div class="text-slate-500 text-xs">{{ $f->created_at->format('H:i A') }}</div>
                        </td>
                        <td class="px-4 py-2 text-purple-400 text-xs font-bold">
                            @if($equipment->tracking_type === 'hours' && $f->hours_logged)
                                {{ number_format($f->hours_logged, 1) }}
                            @elseif($equipment->tracking_type === 'days' && $f->days_logged)
                                {{ number_format($f->days_logged, 0) }}
                            @else
                                --
                            @endif
                        </td>
                        <td class="px-4 py-2 text-white text-xs">{{ number_format($f->liters,1) }} لتر</td>
                        <td class="px-4 py-2 text-slate-400 text-xs">{{ number_format($f->unit_cost,2) }}</td>
                        <td class="px-4 py-2 text-orange-400 font-bold text-xs">{{ number_format($f->total_cost,0) }}</td>
                        <td class="px-4 py-2 text-xs">
                            @if($f->deduct_from_inventory)
                                <span class="badge badge-purple text-xs">من المخزون</span>
                                @if($f->inventoryItem)
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $f->inventoryItem->name_ar }}</div>
                                @endif
                            @else
                                <span class="badge badge-blue text-xs">من الخزينة</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500 text-xs">لا توجد سجلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fuelLogs->hasPages())<div class="px-4 py-2 border-t border-slate-800 text-xs">{{ $fuelLogs->links() }}</div>@endif
    </div>

    {{-- Maintenance Logs --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-white font-bold flex items-center gap-2"><i class="fas fa-wrench text-blue-400"></i> سجلات الصيانة</h3>
            <a href="{{ route('equipment.maintenance.create', $equipment) }}" class="text-amber-400 text-xs hover:text-amber-300">+ إضافة</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-700">
                        @foreach([
                            'التاريخ',
                            'النوع',
                            'الوصف',
                            $equipment->tracking_type_label,
                            'التكلفة'
                        ] as $h)
                            <th class="px-4 py-2 text-right text-slate-400 font-medium text-xs">
                                {{ $h }}
                            </th>
                        @endforeach
                    </tr>           
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($maintenance as $m)
                    <tr class="table-row">
                        <td class="px-4 py-2 text-slate-300 text-xs">{{ $m->maintenance_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2"><span class="badge badge-blue text-xs">{{ $m->type_label }}</span></td>
                        <td class="px-4 py-2 text-slate-300 text-xs">{{ Str::limit($m->description,30) }}</td>
                        <td class="px-4 py-2 text-purple-400 text-xs font-bold">
                            @if($equipment->tracking_type === 'hours' && $m->hours_at_maintenance)
                                {{ number_format($m->hours_at_maintenance, 1) }}
                            @elseif($equipment->tracking_type === 'days' && $m->days_at_maintenance)
                                {{ number_format($m->days_at_maintenance, 0) }}
                            @else
                                --
                            @endif
                        </td>
                        <td class="px-4 py-2 text-red-400 font-bold text-xs">{{ number_format($m->cost,0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500 text-xs">لا توجد سجلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maintenance->hasPages())<div class="px-4 py-2 border-t border-slate-800 text-xs">{{ $maintenance->links() }}</div>@endif
    </div>
</div>
@endsection
