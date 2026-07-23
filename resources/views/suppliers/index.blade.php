@extends('layouts.app')
@section('title', 'الموردون')
@section('content')

@include('partials.page-header', [
    'title'       => 'إدارة الموردين',
    'icon'        => 'fa-truck',
    'createRoute' => 'suppliers.create',
    'createLabel' => 'إضافة مورد',
])

<div class="card p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-40">
            <label class="text-slate-400 text-xs mb-1 block">بحث</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="اسم المورد..."
                class="input-field w-full px-3 py-2 text-sm">
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i> بحث</button>
        <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-white px-3 py-2 text-sm">مسح</a>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','اسم المورد','الهاتف','نوع الدفع','الرصيد','المشتريات','الحالة','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($suppliers as $supplier)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $supplier->id }}</td>
                    <td class="px-4 py-3">
                        <div class="text-white font-medium">{{ $supplier->name }}</div>
                        <div class="text-slate-500 text-xs">{{ $supplier->address }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-300">{{ $supplier->phone ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge badge-blue">{{ ['cash'=>'نقدي','credit'=>'آجل','mixed'=>'مختلط'][$supplier->payment_type] ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 {{ $supplier->balance > 0 ? 'text-red-400' : 'text-green-400' }} font-bold">
                        {{ $supplier->balance > 0 ? 'له' : ($supplier->balance == 0 ? "" : "عليه") }} {{ number_format(abs($supplier->balance), 0) }}
                    </td>
                    <td class="px-4 py-3 text-slate-300">{{ $supplier->purchases_count }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $supplier->is_active ? 'badge-green' : 'badge-gray' }}">
                            {{ $supplier->is_active ? 'نشط' : 'موقف' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue-400 hover:text-blue-300 px-2 py-1 border border-blue-400/30 rounded text-xs transition"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="text-amber-400 hover:text-amber-300 px-2 py-1 border border-amber-400/30 rounded text-xs transition"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">
                    <i class="fas fa-truck text-4xl mb-3 opacity-30"></i><br>لا يوجد موردون مسجلون
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">{{ $suppliers->links() }}</div>
    @endif
</div>
@endsection
