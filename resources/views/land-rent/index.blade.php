@extends('layouts.app')
@section('title', 'إيجار الأرض')
@section('content')

@include('partials.page-header', [
    'title'       => 'إيجار الأرض',
    'icon'        => 'fa-map-marked-alt',
    'createRoute' => 'land-rent.create',
    'createLabel' => 'إضافة دفعة',
])

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['السنة','الشهر','المبلغ','تاريخ الدفع','الحالة','ملاحظات','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($landRents as $r)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $r->year }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ \Carbon\Carbon::create()->month($r->month)->translatedFormat('F') }}</td>
                    <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($r->amount,0) }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $r->payment_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $r->status==='paid'?'badge-green':'badge-yellow' }}">{{ $r->status==='paid'?'مسدد':'معلق' }}</span></td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $r->notes ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('land-rent.show',$r) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('land-rent.edit',$r) }}" class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 border border-amber-400/30 rounded"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-map-marked-alt text-4xl mb-3 opacity-30"></i><br>لا توجد سجلات إيجار</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($landRents->hasPages())
    <div class="px-4 py-3 border-t border-slate-800">
        {{ $landRents->links() }}
    </div>
    @endif
</div>
@endsection
