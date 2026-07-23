@extends('layouts.app')
@section('title', 'سندات القبض والصرف')
@section('content')

@include('partials.page-header', [
    'title'       => 'السندات',
    'icon'        => 'fa-file-invoice-dollar',
    'createRoute' => 'receipts.create',
    'createLabel' => 'إضافة سند',
])

<div class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
    <form method="GET" class="flex-1 flex gap-3">
        <div class="min-w-36">
            <label class="text-slate-400 text-xs mb-1 block">النوع</label>
            <select name="type" class="input-field w-full px-3 py-2 text-sm">
                <option value="">الكل</option>
                <option value="in" {{ request('type')=='in'?'selected':'' }}>قبض</option>
                <option value="out" {{ request('type')=='out'?'selected':'' }}>صرف</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-white px-4 py-2 rounded-lg text-sm mt-auto"><i class="fas fa-search"></i> بحث</button>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','التاريخ','النوع','المبلغ','الجهة/الاسم','البيان','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($receipts as $receipt)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $receipt->id }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $receipt->type==='in'?'badge-green':'badge-red' }}">
                            {{ $receipt->type==='in'?'قبض':'صرف' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold {{ $receipt->type==='in'?'text-green-400':'text-red-400' }}">
                        {{ number_format($receipt->amount,0) }}
                    </td>
                    <td class="px-4 py-3 text-white">{{ $receipt->recipient_name }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ Str::limit($receipt->description, 40) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('receipts.show', $receipt) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('receipts.edit', $receipt) }}" class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 border border-amber-400/30 rounded"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('receipts.destroy',$receipt) }}" method="POST" onsubmit="return confirm('حذف السند؟')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-300 text-xs px-2 py-1 border border-red-400/30 rounded"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-file-invoice-dollar text-4xl mb-3 opacity-30"></i><br>لا توجد سندات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($receipts->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $receipts->links() }}</div>@endif
</div>
@endsection
