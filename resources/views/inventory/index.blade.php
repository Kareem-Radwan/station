@extends('layouts.app')
@section('title', 'المخزون')
@section('content')

    <div class="flex items-center justify-between mb-6">
        @include('partials.page-header', ['title' => 'إدارة المخزون', 'icon' => 'fa-boxes'])
        <a href="{{ route('inventory.create') }}"
            class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2 whitespace-nowrap">
            <i class="fas fa-plus"></i> إضافة مادة
        </a>
    </div>

    {{-- Search --}}
    <div class="card p-4 mb-6">
        <form method="GET" class="flex gap-3 items-end">
            <div class="flex-1">
                <label class="text-slate-400 text-xs mb-1 block">بحث عن مادة</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم المادة..."
                    class="input-field w-full px-3 py-2 text-sm">
            </div>
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-search"></i> بحث
            </button>
            @if(request('search'))
            <a href="{{ route('inventory.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($items as $item)
            @php $isLow = $item->isBelowAlert(); @endphp
            <div
                class="card p-5 rounded-2xl relative overflow-hidden border {{ $isLow ? 'border-red-500/40' : 'border-slate-700/30' }}">
                <div class="absolute top-0 left-0 w-full h-1 {{ $isLow ? 'bg-red-500' : 'bg-green-500' }}"></div>
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-white font-bold text-lg">{{ $item->name_ar }}</h3>
                        <p class="text-slate-500 text-xs">{{ $item->name }}</p>
                    </div>
                    <span class="badge {{ $isLow ? 'badge-red' : 'badge-green' }}">
                        {{ $isLow ? 'منخفض' : 'طبيعي' }}
                    </span>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-400">المخزون الحالي</span>
                        <span class="{{ $isLow ? 'text-red-400' : 'text-amber-400' }} font-bold text-xl">
                            {{ number_format($item->current_stock, 2) }}
                        </span>
                    </div>
                    <p class="text-slate-500 text-xs">{{ $item->unit }} | حد التنبيه:
                        {{ number_format($item->alert_threshold, 1) }}</p>
                    @if ($item->price_per_unit > 0)
                        <p class="text-slate-500 text-xs mt-0.5">سعر الوحدة: <span
                                class="text-amber-400/80">{{ number_format($item->price_per_unit, 2) }} جنية</span></p>
                    @endif
                    {{-- Progress bar --}}
                    @php $pct = $item->alert_threshold > 0 ? min(100, ($item->current_stock / $item->alert_threshold) * 50) : 100; @endphp
                    <div class="mt-2 h-1.5 bg-slate-700 rounded-full">
                        <div class="h-1.5 rounded-full {{ $isLow ? 'bg-red-500' : 'bg-green-500' }}"
                            style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <a href="{{ route('inventory.stock-in', $item) }}"
                        class="flex flex-col items-center justify-center gap-2 p-4 bg-green-500/10 text-green-400 border border-green-500/20 rounded-2xl hover:bg-green-500/20 hover:scale-105 transition-all duration-200">
                        <i class="fas fa-arrow-down text-lg"></i>
                        <span class="text-xs font-medium">وارد</span>
                    </a>

                    <a href="{{ route('inventory.stock-out', $item) }}"
                        class="flex flex-col items-center justify-center gap-2 p-4 bg-red-500/10 text-red-400 border border-red-500/20 rounded-2xl hover:bg-red-500/20 hover:scale-105 transition-all duration-200">
                        <i class="fas fa-arrow-up text-lg"></i>
                        <span class="text-xs font-medium">صادر</span>
                    </a>

                    <a href="{{ route('inventory.movements', $item) }}"
                        class="flex flex-col items-center justify-center gap-2 p-4 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-2xl hover:bg-blue-500/20 hover:scale-105 transition-all duration-200">
                        <i class="fas fa-history text-lg"></i>
                        <span class="text-xs font-medium">الحركات</span>
                    </a>

                    <a href="{{ route('inventory.update-price', $item) }}"
                        class="flex flex-col items-center justify-center gap-2 p-4 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-2xl hover:bg-amber-500/20 hover:scale-105 transition-all duration-200">
                        <i class="fas fa-tag text-lg"></i>
                        <span class="text-xs font-medium">السعر</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    @if($items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
    @endif

@endsection
