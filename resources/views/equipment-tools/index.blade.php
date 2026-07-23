@extends('layouts.app')
@section('title', 'مخزون المعدات')
@section('content')

@include('partials.page-header', [
    'title' => 'مخزون المعدات',
    'icon' => 'fa-tools',
    'createRoute' => 'equipment-tools.create',
    'createLabel' => 'إضافة أداة',
])

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($tools as $tool)
    <div class="card p-5 rounded-2xl relative overflow-hidden border border-slate-700/30">
        <div class="absolute top-0 left-0 w-full h-1 {{ $tool->quantity > 0 ? 'bg-green-500' : 'bg-red-500' }}"></div>
        
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center">
                <i class="fas fa-tools text-blue-400 text-xl"></i>
            </div>
            <span class="badge badge-{{ $tool->quantity > 0 ? 'green' : 'red' }}">
                {{ $tool->quantity > 0 ? 'متوفر' : 'نفذ' }}
            </span>
        </div>

        <h3 class="text-white font-bold mb-1">{{ $tool->name }}</h3>
        <p class="text-slate-400 text-xs mb-3">الوحدة: {{ $tool->unit }}</p>

        <div class="border-t border-slate-700 pt-3 mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">الكمية المتاحة</span>
                <span class="text-green-400 font-bold">{{ number_format($tool->quantity, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">سعر الوحدة</span>
                <span class="text-blue-400">{{ number_format($tool->price_per_unit, 0) }} </span>
            </div>
            <div class="flex justify-between border-t border-slate-800 pt-2 mt-2">
                <span class="text-slate-400 font-bold">القيمة الإجمالية</span>
                <span class="text-amber-400 font-bold">{{ number_format($tool->total_value, 0) }} </span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2 mt-4">
            <a href="{{ route('equipment-tools.show', $tool) }}" 
               class="text-center text-xs px-2 py-2 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-lg hover:bg-blue-500/30 transition">
                <i class="fas fa-eye"></i>
            </a>
            <a href="{{ route('equipment-tools.stock-in', $tool) }}" 
               class="text-center text-xs px-2 py-2 bg-green-500/20 text-green-400 border border-green-500/30 rounded-lg hover:bg-green-500/30 transition">
                <i class="fas fa-plus"></i> إدخال
            </a>
            <a href="{{ route('equipment-tools.stock-out', $tool) }}" 
               class="text-center text-xs px-2 py-2 bg-red-500/20 text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/30 transition">
                <i class="fas fa-minus"></i> صرف
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-3 card p-12 text-center text-slate-500">
        <i class="fas fa-tools text-4xl mb-3 opacity-30"></i><br>
        لا توجد أدوات مسجلة
        <br><a href="{{ route('equipment-tools.create') }}" class="text-amber-400 text-sm mt-2 block hover:text-amber-300">إضافة أول أداة ←</a>
    </div>
    @endforelse
</div>
@if($tools->hasPages())
<div class="mt-4">
    {{ $tools->links() }}
</div>
@endif

@endsection
