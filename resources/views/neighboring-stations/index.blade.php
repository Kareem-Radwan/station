@extends('layouts.app')
@section('title', 'المحطات المجاورة')
@section('content')

    @include('partials.page-header', [
        'title' => 'المحطات المجاورة',
        'icon' => 'fa-industry',
        'actions' => [
            [
                'label' => 'إضافة محطة',
                'route' => route('neighboring-stations.create'),
                'icon' => 'fa-plus',
            ],
        ],
    ])

    <div class="card p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-slate-700">
                    <tr class="text-slate-400 text-sm">
                        <th class="text-right py-3 px-4">#</th>
                        <th class="text-right py-3 px-4">اسم المحطة</th>
                        <th class="text-right py-3 px-4">الشخص المسؤول</th>
                        <th class="text-right py-3 px-4">الهاتف</th>
                        <th class="text-right py-3 px-4">عدد المعاملات</th>
                        <th class="text-right py-3 px-4">الحالة</th>
                        <th class="text-center py-3 px-4">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300">
                    @forelse ($stations as $station)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/30 table-row">
                            <td class="py-3 px-4">{{ $station->id }}</td>
                            <td class="py-3 px-4 font-bold">{{ $station->name }}</td>
                            <td class="py-3 px-4">{{ $station->contact_person ?? '-' }}</td>
                            <td class="py-3 px-4">{{ $station->phone ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="badge badge-blue">{{ $station->transactions_count }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($station->is_active)
                                    <span class="badge badge-green">نشطة</span>
                                @else
                                    <span class="badge badge-gray">غير نشطة</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('neighboring-stations.show', $station) }}"
                                        class="text-blue-400 hover:text-blue-300">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('neighboring-stations.edit', $station) }}"
                                        class="text-amber-400 hover:text-amber-300">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('neighboring-stations.destroy', $station) }}" method="POST"
                                        onsubmit="return confirm('هل أنت متأكد من حذف هذه المحطة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">
                                <i class="fas fa-inbox text-3xl mb-3"></i>
                                <p>لا توجد محطات مجاورة مسجلة</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $stations->links() }}
        </div>
    </div>

@endsection
