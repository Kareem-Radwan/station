@extends('layouts.app')

@section('title', 'الميزانية العمومية')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-600 to-violet-700 flex items-center justify-center shadow-lg">
                    <i class="fas fa-file-invoice text-white text-sm"></i>
                </div>
                الميزانية العمومية
            </h1>
            <p class="text-slate-400 text-sm mt-1">الأصول = الخصوم + حقوق الملكية</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('accounting.trial-balance') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-balance-scale"></i> ميزان المراجعة
            </a>
            <a href="{{ route('accounting.general-ledger') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-book"></i> دفتر الأستاذ
            </a>
            <a href="{{ route('accounting.income-statement') }}" class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                <i class="fas fa-chart-line"></i> قائمة الدخل
            </a>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('accounting.balance-sheet') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-slate-400 mb-1">من تاريخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="input-field w-full px-3 py-2 text-sm">
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-slate-400 mb-1">إلى تاريخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="input-field w-full px-3 py-2 text-sm">
            </div>
            <button type="submit" class="btn-accent px-5 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-filter"></i> عرض
            </button>
            <a href="{{ route('accounting.balance-sheet.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}"
               class="btn-success px-4 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                <i class="fas fa-file-excel"></i> تصدير Excel
            </a>
        </form>
    </div>

    {{-- Balance Check --}}
    @if($is_balanced)
        <div class="alert-success px-4 py-3 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-400 text-xl"></i>
            <div>
                <div class="font-bold">الميزانية متوازنة ✓</div>
                <div class="text-xs opacity-80">الأصول ({{ number_format($total_assets, 2) }}) = الخصوم + حقوق الملكية ({{ number_format($total_liabilities_equity, 2) }})</div>
            </div>
        </div>
    @else
        <div class="alert-error px-4 py-3 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            <div>
                <div class="font-bold">الميزانية غير متوازنة</div>
                <div class="text-xs opacity-80">أصول: {{ number_format($total_assets, 2) }} | خصوم+ملكية: {{ number_format($total_liabilities_equity, 2) }}</div>
            </div>
        </div>
    @endif

    {{-- Two-column Balance Sheet --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT: ASSETS --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-blue-900/40 to-blue-800/20 border-b border-blue-700/30">
                <h2 class="font-bold text-white flex items-center gap-2">
                    <i class="fas fa-building text-blue-400"></i> الأصول
                </h2>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-800">
                    @foreach($assets as $row)
                        <tr class="table-row hover:bg-slate-800/20 transition">
                            <td class="px-5 py-2.5">
                                <div class="text-white text-xs">{{ $row->account_name }}</div>
                                <div class="text-slate-500 text-xs font-mono">{{ $row->account_number }}</div>
                            </td>
                            <td class="px-5 py-2.5 text-left font-mono font-medium text-blue-300">
                                {{ number_format($row->net_balance, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-blue-500/40 bg-blue-900/20">
                        <td class="px-5 py-3 font-bold text-blue-300">إجمالي الأصول</td>
                        <td class="px-5 py-3 text-left font-bold font-mono text-blue-300 text-base">
                            {{ number_format($total_assets, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- RIGHT: LIABILITIES + EQUITY --}}
        <div class="space-y-4">
            {{-- Liabilities --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-red-900/40 to-red-800/20 border-b border-red-700/30">
                    <h2 class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-hand-holding-usd text-red-400"></i> الخصوم
                    </h2>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-800">
                        @foreach($liabilities as $row)
                            <tr class="table-row hover:bg-slate-800/20 transition">
                                <td class="px-5 py-2.5">
                                    <div class="text-white text-xs">{{ $row->account_name }}</div>
                                    <div class="text-slate-500 text-xs font-mono">{{ $row->account_number }}</div>
                                </td>
                                <td class="px-5 py-2.5 text-left font-mono font-medium text-red-300">
                                    {{ number_format($row->net_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-red-700/30 bg-red-900/20">
                            <td class="px-5 py-2 font-bold text-red-300 text-sm">إجمالي الخصوم</td>
                            <td class="px-5 py-2 text-left font-bold font-mono text-red-300">
                                {{ number_format($total_liabilities, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Equity --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-purple-900/40 to-purple-800/20 border-b border-purple-700/30">
                    <h2 class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-users text-purple-400"></i> حقوق الملكية
                    </h2>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-800">
                        @foreach($equity as $row)
                            <tr class="table-row hover:bg-slate-800/20 transition">
                                <td class="px-5 py-2.5">
                                    <div class="text-white text-xs">{{ $row->account_name }}</div>
                                    <div class="text-slate-500 text-xs font-mono">{{ $row->account_number }}</div>
                                </td>
                                <td class="px-5 py-2.5 text-left font-mono font-medium text-purple-300">
                                    {{ number_format($row->net_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        {{-- Net Income row --}}
                        <tr class="bg-green-900/10">
                            <td class="px-5 py-2.5">
                                <div class="text-green-300 text-xs font-medium">صافي الدخل (الفترة الحالية)</div>
                            </td>
                            <td class="px-5 py-2.5 text-left font-mono font-medium {{ $net_income >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ number_format($net_income, 2) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-purple-700/30 bg-purple-900/20">
                            <td class="px-5 py-2 font-bold text-purple-300 text-sm">إجمالي حقوق الملكية</td>
                            <td class="px-5 py-2 text-left font-bold font-mono text-purple-300">
                                {{ number_format($total_equity_with_income, 2) }}
                            </td>
                        </tr>
                        <tr class="border-t-2 border-amber-500/40 bg-slate-800">
                            <td class="px-5 py-3 font-bold text-amber-300">إجمالي الخصوم + حقوق الملكية</td>
                            <td class="px-5 py-3 text-left font-bold font-mono text-amber-300 text-base">
                                {{ number_format($total_liabilities_equity, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
