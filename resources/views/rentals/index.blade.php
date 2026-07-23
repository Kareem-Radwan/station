@extends('layouts.app')
@section('title', 'السيارات المستأجرة')
@section('content')

@include('partials.page-header', [
    'title'       => 'السيارات المستأجرة',
    'icon'        => 'fa-car',
    'createRoute' => 'rentals.create',
    'createLabel' => 'سيارة جديدة',
])

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($rentals as $rental)
    @php $statusColors = ['active'=>'green','expired'=>'gray','cancelled'=>'red']; @endphp
    <div class="card p-5 rounded-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 {{ $rental->status==='active'?'bg-green-500':($rental->status==='expired'?'bg-slate-600':'bg-red-500') }}"></div>
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center">
                <i class="fas fa-car text-amber-400"></i>
            </div>
            <span class="badge badge-{{ $statusColors[$rental->status]??'gray' }}">
                {{ ['active'=>'نشط','expired'=>'منتهي','cancelled'=>'ملغي'][$rental->status]??$rental->status }}
            </span>
        </div>
        <h3 class="text-white font-bold mb-0.5">{{ $rental->equipment_name }}</h3>
        @if($rental->car_number)<p class="text-amber-400 text-xs font-mono mb-1">{{ $rental->car_number }}</p>@endif
        @if($rental->driver_name)<p class="text-slate-400 text-xs mb-2"><i class="fas fa-user text-xs ml-1"></i>{{ $rental->driver_name }}</p>@endif
        @if($rental->supplier)<p class="text-slate-500 text-xs mb-2">{{ $rental->supplier->name }}</p>@endif
        <div class="space-y-1 text-xs border-t border-slate-700 pt-3 mt-3">
            @if($rental->hourly_price)
            <div class="flex justify-between"><span class="text-slate-400">سعر الساعة</span><span class="text-amber-400 font-bold">{{ number_format($rental->hourly_price,0) }} ج</span></div>
            @endif
            @if($rental->driver_allowance)
            <div class="flex justify-between"><span class="text-slate-400">معيشة</span><span class="text-cyan-400 font-bold">{{ number_format($rental->driver_allowance,0) }} ج</span></div>
            @endif
        </div>
        <a href="{{ route('rentals.show',$rental) }}" class="mt-4 w-full block text-center text-xs px-3 py-2 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-lg hover:bg-blue-500/30 transition">
            <i class="fas fa-eye"></i> التفاصيل والورديات
        </a>
    </div>
    @empty
    <div class="col-span-3 card p-12 text-center text-slate-500">
        <i class="fas fa-car text-4xl mb-3 opacity-30"></i><br>لا توجد سيارات مستأجرة
        <br><a href="{{ route('rentals.create') }}" class="text-amber-400 text-sm mt-2 block">إضافة أول سيارة ←</a>
    </div>
    @endforelse
</div>
@if($rentals->hasPages())
<div class="mt-4">
    {{ $rentals->links() }}
</div>
@endif
@endsection
