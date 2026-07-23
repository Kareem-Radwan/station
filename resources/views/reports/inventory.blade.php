@extends('layouts.app')
@section('title', 'تقرير المخزون')
@section('content')

@include('partials.page-header', ['title' => 'تقرير حالة المخزون', 'icon' => 'fa-boxes'])

<div class="card p-4 mb-6 flex justify-between items-center">
    <div class="text-sm text-slate-400">يعرض هذا التقرير الأرصدة الحالية لجميع المواد مع التنبيه على المواد التي وصلت للحد الأدنى.</div>
    <form method="GET">
        <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm"><i class="fas fa-file-excel"></i> تصدير إكسل</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    @foreach($data as $item)
    <div class="card p-5 rounded-2xl relative overflow-hidden border {{ $item->isBelowAlert() ? 'border-red-500/50' : 'border-slate-700/30' }}">
        @if($item->isBelowAlert())
        <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] px-2 py-1 rounded-bl-lg font-bold flex items-center gap-1">
            <i class="fas fa-exclamation-triangle"></i> نقص بالمخزون
        </div>
        @endif
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl {{ $item->isBelowAlert() ? 'bg-red-500/20' : 'bg-blue-500/20' }} flex items-center justify-center">
                <i class="fas fa-box {{ $item->isBelowAlert() ? 'text-red-400' : 'text-blue-400' }}"></i>
            </div>
            <div>
                <h3 class="text-white font-bold">{{ $item->name_ar }}</h3>
                <p class="text-slate-400 text-xs">{{ $item->name_en }}</p>
            </div>
        </div>
        <div class="mt-4 text-center">
            <p class="text-3xl font-bold {{ $item->isBelowAlert() ? 'text-red-400' : 'text-amber-400' }}">
                {{ number_format($item->current_stock, 1) }}
            </p>
            <p class="text-slate-500 text-xs mt-1">{{ $item->unit }}</p>
        </div>
        <div class="border-t border-slate-700/50 mt-4 pt-3 flex justify-between text-xs text-slate-400">
            <span>الحد الأدنى: {{ $item->alert_threshold }}</span>
            <a href="{{ route('inventory.movements', $item) }}" class="text-blue-400 hover:text-blue-300">سجل الحركات ←</a>
        </div>
    </div>
    @endforeach
</div>
@endsection

