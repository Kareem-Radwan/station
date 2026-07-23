@extends('layouts.app')

@section('title', 'تفاصيل الأداة')

@section('content')

    <div class="max-w-7xl mx-auto">

        @include('partials.page-header', [
            'title' => $equipmentTool->name,
            'icon' => 'fa-tools',
            'backRoute' => 'equipment-tools.index',
        ])

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

            <div class="card p-6 h-full">
                <div class="flex justify-between items-center mb-6">

                    <div>
                        <h2 class="text-xl font-bold text-white">
                            {{ $equipmentTool->name }}
                        </h2>

                        <p class="text-slate-400">
                            بيانات الأداة
                        </p>
                    </div>

                    <div class="w-16 h-16 rounded-2xl bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-tools text-blue-400 text-3xl"></i>
                    </div>

                </div>

                <div class="space-y-5">

                    <div>
                        <div class="text-slate-500 text-sm">
                            الوحدة
                        </div>

                        <div class="text-white font-bold text-lg">
                            {{ $equipmentTool->unit }}
                        </div>
                    </div>

                    @if ($equipmentTool->notes)
                        <div>

                            <div class="text-slate-500 text-sm mb-1">
                                الملاحظات
                            </div>

                            <div class="text-slate-300">
                                {{ $equipmentTool->notes }}
                            </div>

                        </div>
                    @endif

                </div>

            </div>


            <div class="stat-card rounded-xl p-6 h-full">

                <div class="flex justify-between">

                    <div>

                        <div class="text-slate-400">
                            الرصيد الحالي
                        </div>

                        <div class="text-5xl font-black text-green-400 mt-4">

                            {{ number_format($equipmentTool->quantity, 2) }}

                        </div>

                        <div class="text-slate-300 mt-3">

                            {{ $equipmentTool->unit }}

                        </div>

                    </div>

                    <div class="w-16 h-16 rounded-2xl bg-green-500/20 flex items-center justify-center">

                        <i class="fas fa-box-open text-3xl text-green-400"></i>

                    </div>

                </div>

            </div>


            <div class="stat-card rounded-xl p-6 h-full">

                <div class="flex justify-between">

                    <div>

                        <div class="text-slate-400">

                            القيمة الحالية

                        </div>

                        <div class="text-4xl font-black text-amber-400 mt-4">

                            {{ number_format($equipmentTool->total_value, 0) }}

                        </div>

                        <div class="mt-4 text-sm text-slate-400">

                            سعر الوحدة

                        </div>

                        <div class="text-blue-400 font-bold">

                            {{ number_format($equipmentTool->price_per_unit, 0) }}

                        </div>

                    </div>

                    <div class="w-16 h-16 rounded-2xl bg-amber-500/20 flex items-center justify-center">

                        <i class="fas fa-coins text-amber-400 text-3xl"></i>

                    </div>

                </div>

            </div>

        </div>


        {{-- Statistics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            <div class="card p-5 text-center">

                <div class="text-slate-400 text-sm">
                    إجمالي الحركات
                </div>

                <div class="text-3xl font-bold text-blue-400 mt-2">

                    {{ $movements->total() }}

                </div>

            </div>

            <div class="card p-5 text-center">

                <div class="text-slate-400 text-sm">
                    إجمالي الإدخال
                </div>

                <div class="text-3xl font-bold text-green-400 mt-2">

                    {{ number_format($equipmentTool->movements()->where('type', 'in')->sum('quantity'), 2) }}

                </div>

            </div>

            <div class="card p-5 text-center">

                <div class="text-slate-400 text-sm">
                    إجمالي الصرف
                </div>

                <div class="text-3xl font-bold text-red-400 mt-2">

                    {{ number_format($equipmentTool->movements()->where('type', 'out')->sum('quantity'), 2) }}

                </div>

            </div>

            <div class="card p-5 text-center">

                <div class="text-slate-400 text-sm">
                    القيمة الحالية
                </div>

                <div class="text-2xl font-bold text-amber-400 mt-2">

                    {{ number_format($equipmentTool->total_value, 0) }}

                </div>

            </div>

        </div>


        {{-- Actions --}}
        <div class="grid md:grid-cols-2 gap-6 mb-8">

            <a href="{{ route('equipment-tools.stock-in', $equipmentTool) }}"
                class="card p-6 border border-green-500/30 hover:border-green-500 transition">

                <div class="flex justify-between items-center">

                    <div>

                        <h3 class="text-white text-xl font-bold mb-2">

                            إدخال كمية

                        </h3>

                        <div class="text-slate-400">

                            شراء أو إضافة للمخزون

                        </div>

                    </div>

                    <div class="w-16 h-16 rounded-2xl bg-green-500/20 flex items-center justify-center">

                        <i class="fas fa-plus text-green-400 text-3xl"></i>

                    </div>

                </div>

            </a>

            <a href="{{ route('equipment-tools.stock-out', $equipmentTool) }}"
                class="card p-6 border border-red-500/30 hover:border-red-500 transition">

                <div class="flex justify-between items-center">

                    <div>

                        <h3 class="text-white text-xl font-bold mb-2">

                            صرف كمية

                        </h3>

                        <div class="text-slate-400">

                            استهلاك أو استخدام

                        </div>

                    </div>

                    <div class="w-16 h-16 rounded-2xl bg-red-500/20 flex items-center justify-center">

                        <i class="fas fa-minus text-red-400 text-3xl"></i>

                    </div>

                </div>

            </a>

        </div>


        {{-- History --}}
        <div class="card overflow-hidden">

            <div class="flex justify-between items-center px-6 py-5 border-b border-slate-700">

                <h2 class="text-xl font-bold text-white">

                    <i class="fas fa-history text-blue-400 ml-2"></i>

                    سجل الحركات

                </h2>

                <span class="badge badge-blue">

                    {{ $movements->total() }} حركة

                </span>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-slate-800">

                        <tr>

                            <th class="px-5 py-4 text-right">التاريخ</th>
                            <th class="px-5 py-4 text-right">النوع</th>
                            <th class="px-5 py-4 text-right">الكمية</th>
                            <th class="px-5 py-4 text-right">سعر الوحدة</th>
                            <th class="px-5 py-4 text-right">القيمة</th>
                            <th class="px-5 py-4 text-right">الرصيد</th>
                            <th class="px-5 py-4 text-right">الخزينة</th>
                            <th class="px-5 py-4 text-right">الملاحظات</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($movements as $movement)
                            <tr class="table-row border-b border-slate-800">

                                <td class="px-5 py-4">
                                    {{ $movement->movement_date->format('Y-m-d') }}
                                </td>

                                <td class="px-5 py-4">

                                    @if ($movement->type == 'in')
                                        <span class="badge badge-green">

                                            <i class="fas fa-arrow-down"></i>

                                            إدخال

                                        </span>
                                    @else
                                        <span class="badge badge-red">

                                            <i class="fas fa-arrow-up"></i>

                                            صرف

                                        </span>
                                    @endif

                                </td>

                                <td class="px-5 py-4">

                                    {{ number_format($movement->quantity, 2) }}

                                    {{ $equipmentTool->unit }}

                                </td>

                                <td class="px-5 py-4">

                                    {{ number_format($movement->price_per_unit, 0) }}

                                </td>

                                <td class="px-5 py-4 {{ $movement->type == 'in' ? 'text-green-400' : 'text-red-400' }}">

                                    {{ number_format($movement->total_cost, 0) }}

                                </td>

                                <td class="px-5 py-4 text-blue-400">

                                    {{ number_format($movement->balance_after, 2) }}

                                </td>

                                <td class="px-5 py-4">

                                    @if ($movement->treasuryTransaction)
                                        <a href="{{ route('treasury.index') }}"
                                            class="text-amber-400 hover:text-amber-300">

                                            #{{ $movement->treasury_transaction_id }}

                                        </a>
                                    @else
                                        -
                                    @endif

                                </td>

                                <td class="px-5 py-4 text-slate-400">

                                    {{ $movement->notes ?: '-' }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="py-16">

                                    <div class="flex flex-col items-center">

                                        <div
                                            class="w-24 h-24 rounded-full bg-slate-800 flex items-center justify-center mb-5">

                                            <i class="fas fa-box-open text-5xl text-slate-600"></i>

                                        </div>

                                        <div class="text-xl text-slate-400">

                                            لا توجد أي حركات لهذه الأداة

                                        </div>

                                        <div class="text-slate-500 mt-2">

                                            قم بإضافة أول عملية إدخال أو صرف.

                                        </div>

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-slate-800">
                {{ $movements->links() }}
            </div>
            @endif

        </div>

    </div>

@endsection
