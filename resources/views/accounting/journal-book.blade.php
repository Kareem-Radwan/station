@extends('layouts.app')

@section('title', 'دفتر اليومية العامة')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-600 to-orange-700 flex items-center justify-center shadow-lg">
                        <i class="fas fa-scroll text-white text-sm"></i>
                    </div>
                    دفتر اليومية العامة
                </h1>
                <p class="text-slate-400 text-sm mt-1">سجل جميع قيود اليومية المرحّلة — {{ $entries->total() }} قيد</p>
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
            </div>
        </div>

        {{-- Filters + Export --}}
        <div class="card p-4">
            <form method="GET" action="{{ route('accounting.journal-book') }}" class="flex flex-wrap gap-4 items-end">
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
                <button type="submit"
                    class="btn-accent px-5 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-filter"></i> عرض
                </button>
                <a href="{{ route('accounting.journal-book.export', ['from_date' => $fromDate, 'to_date' => $toDate]) }}"
                    class="btn-success px-4 py-2 rounded-lg text-white text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> تصدير Excel
                </a>
            </form>
        </div>

        {{-- Journal Entries --}}
        @forelse($entries as $entry)
            @php
                $refLabel = \App\Accounting\Helpers\ReferenceTypeMapper::label($line->reference_type);
                $refIcon = \App\Accounting\Helpers\ReferenceTypeMapper::icon($line->reference_type);
                $refColor = \App\Accounting\Helpers\ReferenceTypeMapper::color($line->reference_type);
                $refUrl = \App\Accounting\Helpers\ReferenceTypeMapper::url($line->reference_type, $line->reference_id);
            @endphp
            <div class="card overflow-hidden">
                {{-- Entry Header --}}
                <div
                    class="px-5 py-3 bg-slate-800/60 border-b border-slate-700 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-mono text-amber-400 font-bold text-sm">{{ $entry->entry_no }}</span>
                        <span class="text-slate-400 text-xs">{{ $entry->date->format('Y-m-d') }}</span>
                        <span class="badge badge-green text-xs">مرحّل</span>

                        {{-- Reference badge — mapped to Arabic label --}}
                        @if ($entry->reference_type)
                            @if ($refUrl)
                                <a href="{{ $refUrl }}" target="_blank"
                                    class="badge badge-{{ $refColor }} text-xs flex items-center gap-1 hover:opacity-80 transition">
                                    <i class="fas {{ $refIcon }}"></i>
                                    {{ $refLabel }}
                                    @if ($entry->reference_id)
                                        <span class="opacity-70">#{{ $entry->reference_id }}</span>
                                    @endif
                                    <i class="fas fa-external-link-alt opacity-50 text-[9px]"></i>
                                </a>
                            @else
                                <span class="badge badge-{{ $refColor }} text-xs flex items-center gap-1">
                                    <i class="fas {{ $refIcon }}"></i>
                                    {{ $refLabel }}
                                    @if ($entry->reference_id)
                                        <span class="opacity-70">#{{ $entry->reference_id }}</span>
                                    @endif
                                </span>
                            @endif
                        @endif
                    </div>
                    <div class="text-slate-300 text-sm font-medium truncate max-w-md">{{ $entry->description }}</div>
                </div>

                {{-- Lines --}}
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-800/50">
                        @foreach ($entry->lines as $line)
                            <tr class="{{ $line->debit > 0 ? '' : 'opacity-80' }}">
                                <td class="px-5 py-2 w-12 text-center text-slate-500 text-xs">
                                    {{ $line->debit > 0 ? 'م' : 'د' }}
                                </td>
                                <td class="px-5 py-2 font-mono text-xs text-slate-400 w-20">
                                    {{ $line->account?->account_number }}</td>
                                <td class="px-5 py-2 text-white">
                                    {{ $line->account?->account_name }}
                                    @if ($line->description && $line->description !== $entry->description)
                                        <span class="text-xs text-slate-400 mr-2">— {{ $line->description }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-2 text-left font-mono font-medium text-blue-300 w-32">
                                    @if ($line->debit > 0)
                                        {{ number_format($line->debit, 2) }}
                                    @endif
                                </td>
                                <td class="px-5 py-2 text-left font-mono font-medium text-orange-300 w-32">
                                    @if ($line->credit > 0)
                                        {{ number_format($line->credit, 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-amber-500/20 bg-slate-800/40">
                            <td colspan="3" class="px-5 py-2 text-xs text-slate-500">
                                {{ $entry->lines->count() }} سطر
                            </td>
                            <td class="px-5 py-2 text-left font-mono text-blue-300 text-xs font-bold">
                                {{ number_format($entry->lines->sum('debit'), 2) }}
                            </td>
                            <td class="px-5 py-2 text-left font-mono text-orange-300 text-xs font-bold">
                                {{ number_format($entry->lines->sum('credit'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @empty
            <div class="card px-6 py-16 text-center text-slate-500">
                <i class="fas fa-inbox text-5xl mb-4 opacity-20"></i>
                <p class="text-lg">لا توجد قيود يومية في هذه الفترة</p>
            </div>
        @endforelse

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $entries->links() }}
        </div>

    </div>
@endsection
