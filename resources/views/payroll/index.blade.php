@extends('layouts.app')
@section('title', 'الرواتب')
@section('content')

@include('partials.page-header', [
    'title'       => 'إدارة الرواتب',
    'icon'        => 'fa-money-bill-wave',
    'createRoute' => 'payroll.calculate',
    'createLabel' => 'احتساب رواتب',
])

<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">الشهر</label>
            <select name="month" class="input-field w-full px-3 py-2 text-sm">
                @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ request('month', now()->month)==$m?'selected':'' }}>
                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                </option>
                @endfor
            </select>
        </div>
        <div class="min-w-32">
            <label class="text-slate-400 text-xs mb-1 block">السنة</label>
            <select name="year" class="input-field w-full px-3 py-2 text-sm">
                @for($y=now()->year;$y>=now()->year-3;$y--)
                <option value="{{ $y }}" {{ request('year', now()->year)==$y?'selected':'' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i> عرض</button>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['الموظف','أيام الحضور','الراتب الأساسي','الإضافي','الخصومات','الصافي','الحالة',''] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($payrolls as $p)
                <tr class="table-row">
                    <td class="px-4 py-3 text-white font-medium">{{ $p->employee->name }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $p->days_attended }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ number_format($p->base_salary,0) }}</td>
                    <td class="px-4 py-3 text-amber-400">{{ $p->overtime_pay ? number_format($p->overtime_pay,0) : '-' }}</td>
                    <td class="px-4 py-3 text-red-400">{{ $p->total_deductions ? number_format($p->total_deductions,0) : '-' }}</td>
                    <td class="px-4 py-3 text-green-400 font-bold text-lg">{{ number_format($p->net_salary,0) }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $p->status==='paid'?'badge-green':'badge-yellow' }}">{{ $p->status==='paid'?'مسدد':'معلق' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('payroll.show',$p) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-slate-500">
                    <i class="fas fa-money-bill-wave text-4xl mb-3 opacity-30"></i><br>لا توجد رواتب لهذا الشهر<br>
                    <a href="{{ route('payroll.calculate') }}" class="text-amber-400 text-sm mt-2 inline-block hover:text-amber-300">احتساب الرواتب ←</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $payrolls->links() }}
    </div>
    @endif
</div>
@endsection
