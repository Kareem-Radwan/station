@extends('layouts.app')
@section('title', 'تقرير تكاليف المعدات')
@section('content')

@include('partials.page-header', ['title' => 'تقرير تكاليف المعدات', 'icon' => 'fa-cog'])

<div class="card p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-slate-400 text-xs mb-1">المعدة المملوكة (اختياري)</label>
            <select name="equipment_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">كل المعدات المملوكة</option>
                @foreach(\App\Models\Equipment::all() as $eq)
                <option value="{{ $eq->id }}" {{ request('equipment_id')==$eq->id?'selected':'' }}>{{ $eq->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">من تاريخ</label>
            <input type="date" name="from_date" value="{{ request('from_date', today()->startOfMonth()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-400 text-xs mb-1">إلى تاريخ</label>
            <input type="date" name="to_date" value="{{ request('to_date', today()->endOfMonth()->toDateString()) }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fas fa-filter"></i> عرض التقرير</button>
            <button type="submit" name="export" value="excel" class="btn-accent text-slate-900 px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fas fa-file-excel"></i> إكسل</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">وقود المعدات المملوكة</p>
        <p class="text-xl font-bold text-orange-400">{{ number_format($totalFuel, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">صيانة المعدات المملوكة</p>
        <p class="text-xl font-bold text-red-400">{{ number_format($totalMaint, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">إيجار المعدات المستأجرة</p>
        <p class="text-xl font-bold text-purple-400">{{ number_format($totalRental, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-slate-700/50">
        <p class="text-slate-400 text-xs mb-1">صيانة المعدات المستأجرة</p>
        <p class="text-xl font-bold text-pink-400">{{ number_format($totalRentalMaint, 0) }}</p>
    </div>
    <div class="stat-card rounded-2xl p-4 border border-amber-500/30">
        <p class="text-slate-400 text-xs mb-1">المجموع الكلي</p>
        <p class="text-xl font-bold text-amber-400">{{ number_format($grandTotal, 0) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['المعدة','النوع','الفئة','وقود','صيانة','إيجار','صيانة إيجار','الإجمالي'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($equipmentSummary as $eq)
                <tr class="table-row">
                    <td class="px-4 py-3 text-white font-medium">{{ $eq['name'] }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $eq['type'] }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $eq['category'] === 'مملوكة' ? 'badge-blue' : 'badge-purple' }}">
                            {{ $eq['category'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-orange-400">{{ $eq['fuel'] > 0 ? number_format($eq['fuel'],0) : '-' }}</td>
                    <td class="px-4 py-3 text-red-400">{{ $eq['maint'] > 0 ? number_format($eq['maint'],0) : '-' }}</td>
                    <td class="px-4 py-3 text-purple-400">{{ $eq['rental_fee'] > 0 ? number_format($eq['rental_fee'],0) : '-' }}</td>
                    <td class="px-4 py-3 text-pink-400">{{ $eq['rental_maint'] > 0 ? number_format($eq['rental_maint'],0) : '-' }}</td>
                    <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($eq['total'],0) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">لا توجد معدات مسجلة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

