@extends('layouts.app')
@section('title', 'سجل الحضور')
@section('content')

@include('partials.page-header', [
    'title'       => 'سجل الحضور والانصراف',
    'icon'        => 'fa-clock',
    'createRoute' => 'attendance.create',
    'createLabel' => 'تسجيل حضور',
])

{{-- Filters --}}
<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-44 flex-1">
            <label class="text-slate-400 text-xs mb-1 block">الموظف</label>
            <select name="employee_id" class="input-field w-full px-3 py-2 text-sm">
                <option value="">كل الموظفين</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ $filterEmployee==$emp->id?'selected':'' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">من</label>
            <input type="date" name="from_date" value="{{ $fromDate }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">إلى</label>
            <input type="date" name="to_date" value="{{ $toDate }}" class="input-field w-full px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i> بحث</button>
        <a href="{{ route('attendance.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
    </form>
</div>

{{-- Accordion Groups --}}
@forelse($grouped as $index => $group)
<div class="card overflow-hidden mb-3" id="accordion-{{ $index }}">
    {{-- Accordion Header --}}
    <button
        type="button"
        onclick="toggleAccordion({{ $index }})"
        class="w-full flex items-center justify-between px-5 py-4 text-right hover:bg-slate-800/40 transition-colors group"
    >
        <div class="flex items-center gap-4">
            {{-- Avatar --}}
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-700 flex items-center justify-center text-slate-900 font-bold text-sm flex-shrink-0">
                {{ mb_substr($group->employee->name, 0, 1) }}
            </div>
            <div class="text-right">
                <a href="{{ route('employees.show', $group->employee) }}"
                   onclick="event.stopPropagation()"
                   class="text-white font-bold hover:text-amber-400 transition-colors">
                    {{ $group->employee->name }}
                </a>
                <p class="text-slate-400 text-xs">{{ $group->employee->position ?? 'موظف' }}</p>
            </div>
        </div>

        {{-- Stats chips --}}
        <div class="flex items-center gap-3 flex-wrap justify-end">
            <span class="badge badge-green text-xs">
                <i class="fas fa-check-circle ml-1"></i>{{ $group->days_present }} حضور
            </span>
            @if($group->days_absent)
            <span class="badge badge-red text-xs">
                <i class="fas fa-times-circle ml-1"></i>{{ $group->days_absent }} غياب
            </span>
            @endif
            <span class="px-2 py-1 rounded-lg bg-slate-700 text-slate-300 text-xs">
                <i class="fas fa-clock text-amber-400 ml-1"></i>{{ number_format($group->total_hours, 1) }}h عمل
            </span>
            @if($group->total_overtime > 0)
            <span class="px-2 py-1 rounded-lg bg-amber-900/40 text-amber-300 text-xs">
                <i class="fas fa-bolt ml-1"></i>{{ number_format($group->total_overtime, 1) }}h إضافي
            </span>
            @endif
            @if($group->total_deductions > 0)
            <span class="px-2 py-1 rounded-lg bg-red-900/40 text-red-300 text-xs">
                <i class="fas fa-minus-circle ml-1"></i>{{ number_format($group->total_deductions, 0) }} خصم
            </span>
            @endif
            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform duration-200 accordion-icon-{{ $index }}"></i>
        </div>
    </button>

    {{-- Accordion Body --}}
    <div id="accordion-body-{{ $index }}" class="hidden border-t border-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/50">
                        @foreach(['التاريخ','الحالة','دخول','خروج','ساعات العمل','إضافي','خصم','ملاحظات',''] as $h)
                        <th class="px-4 py-2.5 text-right text-slate-400 font-medium text-xs">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($group->days as $day)
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        {{-- Date --}}
                        <td class="px-4 py-3 text-slate-300 font-medium whitespace-nowrap">
                            {{ $day->date->format('d/m/Y') }}
                            <span class="text-slate-500 text-xs block">{{ $day->date->translatedFormat('l') }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            <span class="badge text-xs
                                @if($day->status === 'present')  badge-green
                                @elseif($day->status === 'half_day') badge-yellow
                                @else badge-red
                                @endif">
                                {{ $day->status_label }}
                            </span>
                        </td>

                        {{-- Time In --}}
                        <td class="px-4 py-3 text-green-400 font-mono text-xs">{{ $day->time_in ?? '-' }}</td>

                        {{-- Time Out --}}
                        <td class="px-4 py-3 text-red-400 font-mono text-xs">{{ $day->time_out ?? '-' }}</td>

                        {{-- Hours worked --}}
                        <td class="px-4 py-3">
                            @if($day->hours_worked)
                                <span class="font-bold {{ $day->hours_worked >= 10 ? 'text-green-400' : 'text-yellow-400' }}">
                                    {{ number_format($day->hours_worked, 2) }}h
                                </span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>

                        {{-- Overtime --}}
                        <td class="px-4 py-3">
                            @if($day->overtime_hours > 0)
                                <span class="text-amber-400 font-bold">{{ number_format($day->overtime_hours, 1) }}h</span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>

                        {{-- Deduction --}}
                        <td class="px-4 py-3">
                            @if($day->deduction > 0)
                                <span class="text-red-400 font-bold">{{ number_format($day->deduction, 0) }}</span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </td>

                        {{-- Notes --}}
                        <td class="px-4 py-3 text-slate-400 text-xs max-w-32 truncate">{{ $day->notes ?? '-' }}</td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <a href="{{ route('attendance.edit', $day->attendance) }}"
                               class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 border border-amber-400/30 rounded transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Totals row --}}
                <tfoot>
                    <tr class="bg-slate-800/60 border-t border-slate-600">
                        <td class="px-4 py-2.5 text-slate-400 font-bold text-xs" colspan="2">الإجمالي</td>
                        <td colspan="2"></td>
                        <td class="px-4 py-2.5 text-green-400 font-bold text-xs">{{ number_format($group->total_hours, 2) }}h</td>
                        <td class="px-4 py-2.5 text-amber-400 font-bold text-xs">
                            @if($group->total_overtime > 0){{ number_format($group->total_overtime, 1) }}h @endif
                        </td>
                        <td class="px-4 py-2.5 text-red-400 font-bold text-xs">
                            @if($group->total_deductions > 0){{ number_format($group->total_deductions, 0) }} -@endif
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@empty
<div class="card p-12 text-center">
    <i class="fas fa-clock text-5xl text-slate-700 mb-4"></i>
    <p class="text-slate-500 text-lg">لا توجد سجلات حضور في هذه الفترة</p>
    <a href="{{ route('attendance.create') }}" class="inline-block mt-4 btn-accent text-slate-900 font-bold px-5 py-2 rounded-lg text-sm">
        <i class="fas fa-plus ml-1"></i> تسجيل حضور
    </a>
</div>
@endforelse

@push('scripts')
<script>
function toggleAccordion(index) {
    const body = document.getElementById('accordion-body-' + index);
    const icon = document.querySelector('.accordion-icon-' + index);
    const isOpen = !body.classList.contains('hidden');

    if (isOpen) {
        body.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    } else {
        body.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    }
}

// Auto-open first accordion if only one employee shown
document.addEventListener('DOMContentLoaded', function () {
    const count = {{ $grouped->count() }};
    if (count === 1) {
        toggleAccordion(0);
    }
});
</script>
@endpush

@endsection
