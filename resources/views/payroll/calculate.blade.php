@extends('layouts.app')
@section('title', 'احتساب الرواتب')
@section('content')

@include('partials.page-header', ['title' => 'احتساب الرواتب الشهرية', 'icon' => 'fa-calculator'])

<div class="max-w-xl">
    <form action="{{ route('payroll.calculate.store') }}" method="POST" class="card p-6 space-y-5">
        @csrf
        <div class="bg-amber-900/20 border border-amber-500/30 rounded-xl p-4 text-sm text-amber-300">
            <i class="fas fa-exclamation-triangle ml-2"></i>
            سيتم احتساب رواتب جميع الموظفين النشطين بناءً على سجلات الحضور للشهر المحدد. لن تتكرر إذا كانت موجودة مسبقاً.
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">الشهر <span class="text-red-400">*</span></label>
                <select name="month" required class="input-field w-full px-3 py-2.5 text-sm">
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ (old('month') ?? now()->month)==$m?'selected':'' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-slate-400 text-sm mb-1.5">السنة <span class="text-red-400">*</span></label>
                <select name="year" required class="input-field w-full px-3 py-2.5 text-sm">
                    @for($y=now()->year;$y>=now()->year-3;$y--)
                    <option value="{{ $y }}" {{ (old('year') ?? now()->year)==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="bg-slate-800/50 rounded-xl p-4 text-xs text-slate-400 space-y-1">
            <p><i class="fas fa-info-circle text-blue-400 ml-1"></i><strong class="text-slate-300">آلية الحساب:</strong></p>
            <p>• ساعات العمل الطبيعية = 10 ساعات (8:00 صباحًا - 6:00 مساءً)</p>
            <p>• الإضافي = ساعات العمل الفعلية − 10</p>
            <p>• الصافي = الأساسي + (الإضافي × المعدل) − الخصومات</p>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-accent text-slate-900 font-bold px-6 py-2.5 rounded-lg text-sm"><i class="fas fa-calculator"></i> احتساب الرواتب</button>
            <a href="{{ route('payroll.index') }}" class="text-slate-400 hover:text-white text-sm px-4 py-2.5 rounded-lg border border-slate-700 transition">إلغاء</a>
        </div>
    </form>
</div>
@endsection
