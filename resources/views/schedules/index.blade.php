@extends('layouts.app')
@section('title', 'جدول العمليات')
@section('content')

@include('partials.page-header', [
    'title'       => 'جدول العمليات الأسبوعي',
    'icon'        => 'fa-calendar-alt',
    'createRoute' => 'schedules.create',
    'createLabel' => 'إضافة جدول',
])

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['الأسبوع','تبدأ من','تنتهي في','الحالة','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($schedules as $s)
                <tr class="table-row">
                    <td class="px-4 py-3 text-white font-medium">أسبوع {{ $s->week_number }} ({{ $s->year }})</td>
                    <td class="px-4 py-3 text-slate-300">{{ $s->start_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $s->end_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $s->status==='published'?'badge-green':($s->status==='draft'?'badge-yellow':'badge-gray') }}">{{ ['draft'=>'مسودة','published'=>'منشور','completed'=>'مكتمل'][$s->status]??$s->status }}</span></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('schedules.show',$s) }}" class="text-blue-400 hover:text-blue-300 text-xs px-2 py-1 border border-blue-400/30 rounded"><i class="fas fa-eye"></i> التفاصيل</a>
                            @if($s->status === 'draft')
                            <a href="{{ route('schedules.edit',$s) }}" class="text-amber-400 hover:text-amber-300 text-xs px-2 py-1 border border-amber-400/30 rounded"><i class="fas fa-edit"></i> تعديل</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500"><i class="fas fa-calendar-alt text-4xl mb-3 opacity-30"></i><br>لا توجد جداول</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($schedules->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $schedules->links() }}</div>@endif
</div>
@endsection
