@extends('layouts.app')
@section('title', 'كشف راتب')

@section('content')

    <div class="flex flex-col lg:flex-row items-start justify-between gap-6 mb-6">

        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('payroll.index') }}" class="text-slate-400 hover:text-white text-sm">
                    الرواتب
                </a>

                <i class="fas fa-chevron-left text-slate-600 text-xs"></i>

                <span class="text-white font-bold">
                    {{ $payroll->employee->name }}
                    -
                    {{ $payroll->month }}/{{ $payroll->year }}
                </span>
            </div>

            <span class="badge {{ $payroll->status === 'paid' ? 'badge-green' : 'badge-yellow' }}">
                {{ $payroll->status === 'paid' ? 'مسدد' : 'معلق' }}
            </span>
        </div>

    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Payslip --}}
        <div class="xl:col-span-2">

            <div class="card overflow-hidden">

                <div class="bg-gradient-to-l from-indigo-900/40 to-slate-800 px-6 py-5 border-b border-slate-700">

                    <div class="flex justify-between items-center">

                        <div>
                            <h2 class="text-xl font-bold text-white">
                                {{ $payroll->employee->name }}
                            </h2>

                            <p class="text-slate-400 text-sm mt-1">
                                {{ $payroll->employee->position ?? 'موظف' }}
                            </p>
                        </div>

                        <div class="text-left">
                            <div class="text-slate-500 text-xs">
                                الفترة
                            </div>

                            <div class="text-amber-400 font-bold">
                                {{ $payroll->month }}/{{ $payroll->year }}
                            </div>
                        </div>

                    </div>

                </div>

                <div class="p-6">

                    <div class="grid md:grid-cols-2 gap-4">

                        <div class="bg-slate-800/60 rounded-xl p-4">
                            <div class="text-slate-400 text-xs mb-1">
                                الراتب الأساسي
                            </div>

                            <div class="font-bold text-white">
                                {{ number_format($payroll->base_salary, 0) }} جنيه
                            </div>
                        </div>

                        <div class="bg-slate-800/60 rounded-xl p-4">
                            <div class="text-slate-400 text-xs mb-1">
                                أيام الحضور
                            </div>

                            <div class="font-bold text-white">
                                {{ $payroll->days_attended }} يوم
                            </div>
                        </div>

                        <div class="bg-slate-800/60 rounded-xl p-4">
                            <div class="text-slate-400 text-xs mb-1">
                                ساعات الإضافي
                            </div>

                            <div class="font-bold text-amber-400">
                                {{ number_format($payroll->overtime_hours ?? 0, 1) }} ساعة
                            </div>
                        </div>

                        <div class="bg-slate-800/60 rounded-xl p-4">
                            <div class="text-slate-400 text-xs mb-1">
                                بدل الإضافي
                            </div>

                            <div class="font-bold text-amber-400">
                                {{ number_format($payroll->overtime_pay ?? 0, 0) }} جنيه
                            </div>
                        </div>

                        <div class="md:col-span-2 bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                            <div class="text-slate-400 text-xs mb-1">
                                إجمالي الخصومات
                            </div>

                            <div class="font-bold text-red-400">
                                {{ number_format($payroll->total_deductions ?? 0, 0) }} جنيه
                            </div>
                        </div>

                    </div>

                    <div class="mt-6 border-t border-slate-700 pt-6">

                        <div class="bg-green-500/10 border border-green-500/20 rounded-2xl p-5">

                            <div class="text-slate-400 mb-2">
                                صافي الراتب
                            </div>

                            <div class="text-4xl font-extrabold text-green-400">
                                {{ number_format($payroll->net_salary, 0) }}
                                <span class="text-lg text-slate-400">
                                    جنيه
                                </span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Actions --}}
        <div>

            @php
                $totalBorrowRemaining = $payroll->employee->borrows->sum('remaining_amount');
            @endphp

            @if ($totalBorrowRemaining > 0)
                <div class="card p-5 mb-4">

                    <div class="flex justify-between items-center">

                        <span class="text-red-300 font-bold">
                            السلف المستحقة
                        </span>

                        <span class="text-red-400 font-bold text-xl">
                            {{ number_format($totalBorrowRemaining, 0) }} جنيه
                        </span>

                    </div>

                </div>
            @endif

            @if ($payroll->status !== 'paid')

                <form action="{{ route('payroll.mark-paid', $payroll) }}" method="POST">

                    @csrf
                    @method('PATCH')

                    <div class="card p-5">

                        <h3 class="font-bold text-white mb-4">
                            صرف الراتب
                        </h3>

                        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">

                            @foreach ($payroll->employee->borrows as $index => $borrow)
                                <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">

                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="text-slate-400">
                                            المتبقي
                                        </span>

                                        <span class="font-bold text-red-400">
                                            {{ number_format($borrow->remaining_amount, 0) }}
                                        </span>
                                    </div>

                                    <input type="hidden" name="borrow_deductions[{{ $index }}][borrow_id]"
                                        value="{{ $borrow->id }}">

                                    <input type="number" min="0" max="{{ $borrow->remaining_amount }}"
                                        step="0.01" value="0" name="borrow_deductions[{{ $index }}][amount]"
                                        class="input-field w-full" placeholder="قيمة الخصم">

                                </div>
                            @endforeach

                        </div>

                        <button type="submit" class="btn-accent w-full mt-5 py-3 text-slate-900 font-bold rounded-xl">

                            <i class="fas fa-check-circle ml-2"></i>
                            تأكيد صرف الراتب

                        </button>

                    </div>

                </form>

            @endif

        </div>

    </div>

@endsection
