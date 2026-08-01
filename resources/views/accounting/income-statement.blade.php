@extends('layouts.app')

@section('title', 'قائمة الدخل')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-600 to-emerald-700 flex items-center justify-center shadow-lg">
                        <i class="fas fa-chart-line text-white text-sm"></i>
                    </div>
                    قائمة الدخل
                </h1>
                <p class="text-slate-400 text-sm mt-1">الإيرادات − المصروفات = صافي الدخل | {{ $period }}</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('accounting.trial-balance') }}"
                    class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                    <i class="fas fa-balance-scale"></i> ميزان المراجعة
                </a>
                <a href="{{ route('accounting.general-ledger') }}"
                    class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                    <i class="fas fa-book"></i> دفتر الأستاذ
                </a>
                <a href="{{ route('accounting.balance-sheet') }}"
                    class="btn-primary px-3 py-2 rounded-lg text-sm text-white font-medium flex items-center gap-2">
                    <i class="fas fa-file-invoice"></i> الميزانية العمومية
                </a>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="card p-4">
            <form method="GET" action="{{ route('accounting.income-statement') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs text-slate-400 mb-1">من تاريخ</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs text-slate-400 mb-1">إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ $toDate }}"
                        class="input-field w-full px-3 py-2 text-sm">
                </div>
                <button type="submit" class="btn-accent px-5 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-filter"></i> عرض
                </button>
                <a href="{{ route('accounting.income-statement.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}"
                   class="btn-success px-4 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> تصدير Excel
                </a>
            </form>
        </div>

        {{-- Net Income Hero Card --}}
        <div class="card p-6 border {{ $is_profitable ? 'border-green-500/30' : 'border-red-500/30' }}">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-slate-400 text-sm mb-1">صافي الدخل للفترة</div>
                    <div class="text-4xl font-bold {{ $is_profitable ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format(abs($net_income), 2) }}
                        <span class="text-lg font-normal text-slate-400 mr-1">جنيه</span>
                    </div>
                    <div class="mt-2 text-sm {{ $is_profitable ? 'text-green-400' : 'text-red-400' }}">
                        <i class="fas {{ $is_profitable ? 'fa-arrow-up' : 'fa-arrow-down' }} ml-1"></i>
                        {{ $is_profitable ? 'ربح' : 'خسارة' }}
                    </div>
                </div>
                <div class="flex gap-6 text-center">
                    <div>
                        <div class="text-xs text-slate-400 mb-1">إجمالي الإيرادات</div>
                        <div class="text-2xl font-bold text-green-400">{{ number_format($total_revenue, 2) }}</div>
                    </div>
                    <div class="w-px bg-slate-700"></div>
                    <div>
                        <div class="text-xs text-slate-400 mb-1">إجمالي المصروفات</div>
                        <div class="text-2xl font-bold text-red-400">{{ number_format($total_expenses, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Progress bar --}}
            @if ($total_revenue > 0)
                @php $expenseRatio = min(100, ($total_expenses / $total_revenue) * 100); @endphp
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-slate-400 mb-1">
                        <span>نسبة المصروفات إلى الإيرادات</span>
                        <span>{{ number_format($expenseRatio, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all {{ $expenseRatio > 90 ? 'bg-red-500' : ($expenseRatio > 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                            style="width: {{ $expenseRatio }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Two-column layout: Revenue | Expenses --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Revenue --}}
            <div class="card overflow-hidden">
                <div
                    class="px-5 py-4 bg-gradient-to-r from-green-900/40 to-green-800/20 border-b border-green-700/30 flex items-center justify-between">
                    <h2 class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-arrow-circle-up text-green-400"></i> الإيرادات
                    </h2>
                    <span class="text-green-400 font-bold font-mono">{{ number_format($total_revenue, 2) }}</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-800">
                        @forelse($revenue as $row)
                            <tr class="table-row hover:bg-slate-800/20 transition">
                                <td class="px-5 py-2.5">
                                    <div class="text-white text-xs">{{ $row->account_name }}</div>
                                    <div class="text-slate-500 text-xs font-mono">{{ $row->account_number }}</div>
                                </td>
                                <td class="px-5 py-2.5 text-left font-mono font-medium text-green-300">
                                    {{ number_format($row->net_balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-slate-500 text-sm">
                                    لا توجد إيرادات مرحّلة في هذه الفترة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-green-500/40 bg-green-900/20">
                            <td class="px-5 py-3 font-bold text-green-300">إجمالي الإيرادات</td>
                            <td class="px-5 py-3 text-left font-bold font-mono text-green-300 text-base">
                                {{ number_format($total_revenue, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Expenses --}}
            <div class="card overflow-hidden">
                <div
                    class="px-5 py-4 bg-gradient-to-r from-red-900/40 to-red-800/20 border-b border-red-700/30 flex items-center justify-between">
                    <h2 class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-arrow-circle-down text-red-400"></i> المصروفات
                    </h2>
                    <span class="text-red-400 font-bold font-mono">{{ number_format($total_expenses, 2) }}</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-800">
                        @forelse($expenses as $row)
                            <tr class="table-row hover:bg-slate-800/20 transition">
                                <td class="px-5 py-2.5">
                                    <div class="text-white text-xs">{{ $row->account_name }}</div>
                                    <div class="text-slate-500 text-xs font-mono">{{ $row->account_number }}</div>
                                </td>
                                <td class="px-5 py-2.5 text-left font-mono font-medium text-red-300">
                                    {{ number_format($row->net_balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-slate-500 text-sm">
                                    لا توجد مصروفات مرحّلة في هذه الفترة
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-red-500/40 bg-red-900/20">
                            <td class="px-5 py-3 font-bold text-red-300">إجمالي المصروفات</td>
                            <td class="px-5 py-3 text-left font-bold font-mono text-red-300 text-base">
                                {{ number_format($total_expenses, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        {{-- Summary Row --}}
        <div
            class="card p-5 border {{ $is_profitable ? 'border-green-500/30 bg-green-900/10' : 'border-red-500/30 bg-red-900/10' }}">
            <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
                <span class="text-slate-300 font-medium">
                    إجمالي الإيرادات ({{ number_format($total_revenue, 2) }})
                    − إجمالي المصروفات ({{ number_format($total_expenses, 2) }})
                    = <strong class="{{ $is_profitable ? 'text-green-400' : 'text-red-400' }}">
                        صافي الدخل {{ number_format($net_income, 2) }} جنيه
                    </strong>
                </span>
                <span class="badge {{ $is_profitable ? 'badge-green' : 'badge-red' }} text-sm px-4 py-1">
                    <i class="fas {{ $is_profitable ? 'fa-thumbs-up' : 'fa-thumbs-down' }} ml-2"></i>
                    {{ $is_profitable ? 'نتيجة إيجابية' : 'نتيجة سلبية' }}
                </span>
            </div>
        </div>

    </div>
@endsection
