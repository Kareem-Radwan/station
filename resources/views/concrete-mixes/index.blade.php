@extends('layouts.app')
@section('title', 'الخلطات الخرسانية')
@section('content')

<div class="flex items-center justify-between mb-6">
    @include('partials.page-header', ['title' => 'الخلطات الخرسانية', 'icon' => 'fa-flask'])
    <div class="flex gap-3">
        <a href="{{ route('mix-recipes.index') }}"
            class="bg-slate-700 hover:bg-slate-600 text-slate-100 font-medium px-4 py-2 rounded-lg text-sm flex items-center gap-2 whitespace-nowrap transition">
            <i class="fas fa-cog"></i> إدارة وصفات الخلطات
        </a>
        <a href="{{ route('concrete-mixes.create') }}"
            class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2 whitespace-nowrap">
            <i class="fas fa-plus"></i> خلطة جديدة
        </a>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/50 border-b border-slate-700">
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">#</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">المقاومة</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">اسمنت / م³</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الوصف</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">الحالة</th>
                    <th class="px-4 py-3 text-right text-slate-400 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($mixes as $mix)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-500">{{ $mix->id }}</td>
                    <td class="px-4 py-3">
                        <span class="text-amber-400 font-bold text-base">{{ $mix->strength }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-300 font-medium">
                        {{ number_format($mix->cement_per_m3, 0) }} كغ/م³
                    </td>
                    <td class="px-4 py-3 text-slate-400">{{ $mix->description ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $mix->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $mix->is_active ? 'مفعّل' : 'معطّل' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <form action="{{ route('concrete-mixes.toggle-active', $mix) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="text-xs px-3 py-1.5 rounded-lg border transition
                                    {{ $mix->is_active
                                        ? 'bg-red-500/10 text-red-400 border-red-500/30 hover:bg-red-500/20'
                                        : 'bg-green-500/10 text-green-400 border-green-500/30 hover:bg-green-500/20' }}">
                                <i class="fas {{ $mix->is_active ? 'fa-ban' : 'fa-check' }} ml-1"></i>
                                {{ $mix->is_active ? 'تعطيل' : 'تفعيل' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                        <i class="fas fa-flask text-3xl mb-3 block opacity-30"></i>
                        لا توجد خلطات بعد — أضف خلطة جديدة
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
