@extends('layouts.app')
@section('title', 'المساهمون')
@section('content')

    @include('partials.page-header', [
        'title' => 'إدارة المساهمين',
        'icon' => 'fa-handshake',
        'createRoute' => 'contributors.create',
        'createLabel' => 'إضافة مساهم',
    ])

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card rounded-xl p-4">
            <p class="text-slate-400 text-xs mb-1">إجمالي رأس المال</p>
            <p class="text-white font-bold text-xl">{{ number_format($totalShareAmount, 0) }}</p>
            <p class="text-slate-500 text-xs mt-0.5">جنية</p>
        </div>
        <div class="stat-card rounded-xl p-4">
            <p class="text-slate-400 text-xs mb-1">إجمالي المدفوعات</p>
            <p class="text-green-400 font-bold text-xl">{{ number_format($totalPaid, 0) }}</p>
            <p class="text-slate-500 text-xs mt-0.5">جنية</p>
        </div>
        <div class="stat-card rounded-xl p-4">
            <p class="text-slate-400 text-xs mb-1">المتبقي</p>
            <p class="{{ $totalOutstanding > 0 ? 'text-red-400' : 'text-green-400' }} font-bold text-xl">
                {{ number_format($totalOutstanding, 0) }}</p>
            <p class="text-slate-500 text-xs mt-0.5">جنية</p>
        </div>
        <div class="stat-card rounded-xl p-4">
            <p class="text-slate-400 text-xs mb-1">إجمالي الحصص</p>
            <p class="text-amber-400 font-bold text-xl">{{ number_format($totalSharePercent, 1) }}%</p>
            <p class="text-slate-500 text-xs mt-0.5">من المساهمين النشطين</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="text-slate-400 text-xs mb-1 block">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم أو هاتف..."
                    class="input-field w-full px-3 py-2 text-sm">
            </div>
            <div class="min-w-36">
                <label class="text-slate-400 text-xs mb-1 block">الحالة</label>
                <select name="status" class="input-field w-full px-3 py-2 text-sm">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>موقف</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-search"></i> بحث
            </button>
            <a href="{{ route('contributors.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 bg-slate-800/50">
                        <th class="px-4 py-3 text-right text-slate-400 font-medium">#</th>
                        <th class="px-4 py-3 text-right text-slate-400 font-medium">المساهم</th>
                        <th class="px-4 py-3 text-right text-slate-400 font-medium">قيمة الحصة</th>
                        <th class="px-4 py-3 text-right text-slate-400 font-medium">المدفوع</th>
                        <th class="px-4 py-3 text-right text-slate-400 font-medium">الحالة</th>
                        <th class="px-4 py-3 text-right text-slate-400 font-medium">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($contributors as $c)
                        @php
                            $paid = $c->getTotalPaid();
                            $outstanding = $c->getOutstandingBalance();
                            $paidPct = $c->getPaidPercentage();
                        @endphp
                        <tr class="table-row transition-colors">
                            <td class="px-4 py-3 text-slate-500">{{ $c->id }}</td>
                            <td class="px-4 py-3">
                                <div class="text-white font-medium">{{ $c->name }}</div>
                                <div class="text-slate-500 text-xs">{{ $c->phone ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ number_format($c->share_amount + $paid, 0) }}</td>
                            <td class="px-4 py-3 text-green-400 font-medium">{{ number_format($paid, 0) }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $c->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $c->is_active ? 'نشط' : 'موقف' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('contributors.show', $c) }}"
                                        class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 rounded border border-blue-400/30 hover:border-blue-400/60 transition">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('contributors.edit', $c) }}"
                                        class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 rounded border border-amber-400/30 hover:border-amber-400/60 transition">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('contributor-payments.create') }}?contributor_id={{ $c->id }}"
                                        class="text-green-400 hover:text-green-300 text-xs px-2 py-1 rounded border border-green-400/30 hover:border-green-400/60 transition"
                                        title="تسجيل دفعة">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-slate-500">
                                <i class="fas fa-handshake text-4xl mb-3 opacity-30"></i>
                                <p>لا يوجد مساهمون مسجلون</p>
                                <a href="{{ route('contributors.create') }}"
                                    class="text-amber-400 text-sm mt-2 block hover:text-amber-300">
                                    إضافة أول مساهم ←
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($contributors->hasPages())
            <div class="px-4 py-3 border-t border-slate-800">
                {{ $contributors->links() }}
            </div>
        @endif
    </div>

@endsection
