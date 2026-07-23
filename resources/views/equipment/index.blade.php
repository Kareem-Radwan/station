@extends('layouts.app')
@section('title', 'المعدات')
@section('content')

@include('partials.page-header', [
    'title'       => 'المعدات المملوكة',
    'icon'        => 'fa-cog',
    'createRoute' => 'equipment.create',
    'createLabel' => 'إضافة معدة',
])

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
    @forelse($equipment as $eq)
    @php
        $totalCost = ($eq->fuel_logs_sum_total_cost ?? 0) + ($eq->maintenance_sum_cost ?? 0);
        $statusColors = ['active'=>'green','maintenance'=>'yellow','inactive'=>'gray'];
        $typeIcons = ['loader'=>'fa-truck-pickup','mixer'=>'fa-blender','service_vehicle'=>'fa-car','pump'=>'fa-tint'];
    @endphp
    <div class="card p-5 rounded-2xl relative overflow-hidden border {{ $eq->status==='active'?'border-green-500/20':'border-slate-700/30' }}">
        <div class="absolute top-0 left-0 w-full h-1 {{ $eq->status==='active'?'bg-green-500':($eq->status==='maintenance'?'bg-yellow-500':'bg-slate-600') }}"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center">
                <i class="fas {{ $typeIcons[$eq->type] ?? 'fa-cog' }} text-amber-400 text-xl"></i>
            </div>
            <span class="badge badge-{{ $statusColors[$eq->status]??'gray' }}">{{ $eq->status_label }}</span>
        </div>
        <h3 class="text-white font-bold mb-1">{{ $eq->name }}</h3>
        <p class="text-slate-400 text-xs mb-1">{{ $eq->type_label }}</p>
        @if($eq->model)<p class="text-slate-500 text-xs mb-3">{{ $eq->model }}</p>@endif
        <div class="border-t border-slate-700 pt-3 mt-3 space-y-1 text-xs">
            <div class="flex justify-between"><span class="text-slate-400">وقود</span><span class="text-orange-400">{{ number_format($eq->fuel_logs_sum_total_cost ?? 0, 0) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">صيانة</span><span class="text-red-400">{{ number_format($eq->maintenance_sum_cost ?? 0, 0) }}</span></div>
            <div class="flex justify-between border-t border-slate-800 pt-1 mt-1"><span class="text-slate-400 font-bold">الإجمالي</span><span class="text-amber-400 font-bold">{{ number_format($totalCost, 0) }}</span></div>
        </div>
        <a href="{{ route('equipment.show', $eq) }}" class="mt-4 w-full text-center block text-xs px-3 py-2 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-lg hover:bg-blue-500/30 transition">
            <i class="fas fa-eye"></i> التفاصيل
        </a>
    </div>
    @empty
    <div class="col-span-4 card p-12 text-center text-slate-500">
        <i class="fas fa-cog text-4xl mb-3 opacity-30"></i><br>لا توجد معدات مسجلة
        <br><a href="{{ route('equipment.create') }}" class="text-amber-400 text-sm mt-2 block hover:text-amber-300">إضافة أول معدة ←</a>
    </div>
    @endforelse
</div>
@if($equipment->hasPages())
<div class="mt-4">
    {{ $equipment->links() }}
</div>
@endif
@endsection
