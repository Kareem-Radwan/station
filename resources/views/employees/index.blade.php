@extends('layouts.app')
@section('title', 'الموظفون')
@section('content')

@include('partials.page-header', [
    'title'       => 'إدارة الموظفين',
    'icon'        => 'fa-user-tie',
    'createRoute' => 'employees.create',
    'createLabel' => 'إضافة موظف',
])

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    @foreach(['#','الاسم','المنصب','الراتب الأساسي','معدل الإضافي','الحضور','الحالة','إجراءات'] as $h)
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($employees as $emp)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $emp->id }}</td>
                    <td class="px-4 py-3">
                        <div class="text-white font-medium">{{ $emp->name }}</div>
                        <div class="text-slate-500 text-xs">{{ $emp->phone }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-300">{{ $emp->position ?? '-' }}</td>
                    <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($emp->base_salary,0) }}</td>
                    <td class="px-4 py-3 text-slate-400">{{ $emp->overtime_rate ? number_format($emp->overtime_rate,2) : '-' }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $emp->attendance_count }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $emp->is_active?'badge-green':'badge-gray' }}">{{ $emp->is_active?'نشط':'موقف' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('employees.show',$emp) }}" class="text-blue-400 hover:text-blue-300 px-2 py-1 border border-blue-400/30 rounded text-xs"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('employees.edit',$emp) }}" class="text-amber-400 hover:text-amber-300 px-2 py-1 border border-amber-400/30 rounded text-xs"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-slate-500">
                    <i class="fas fa-user-tie text-4xl mb-3 opacity-30"></i><br>لا يوجد موظفون مسجلون
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())<div class="px-4 py-3 border-t border-slate-800">{{ $employees->links() }}</div>@endif
</div>
@endsection
