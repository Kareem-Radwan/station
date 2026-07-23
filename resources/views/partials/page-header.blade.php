{{-- Reusable page header partial --}}
{{-- Usage: @include('partials.page-header', ['title' => '...', 'icon' => 'fa-users', 'createRoute' => 'customers.create', 'createLabel' => 'إضافة عميل']) --}}
{{-- Or with actions array: @include('partials.page-header', ['title' => '...', 'icon' => 'fa-users', 'actions' => [['label' => 'إضافة', 'route' => '...', 'icon' => 'fa-plus']]]) --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-500/30 flex items-center justify-center border border-primary-500/50">
                <i class="fas {{ $icon ?? 'fa-circle' }} text-amber-400"></i>
            </div>
            {{ $title ?? 'الصفحة' }}
        </h1>
        @isset($subtitle)
        <p class="text-slate-400 text-sm mt-1 mr-14">{{ $subtitle }}</p>
        @endisset
    </div>
    <div class="flex items-center gap-3">
        @isset($createRoute)
        <a href="{{ route($createRoute) }}" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-plus"></i> {{ $createLabel ?? 'إضافة جديد' }}
        </a>
        @endisset
        @isset($actions)
            @foreach($actions as $action)
                <a href="{{ $action['route'] }}" class="btn-accent text-slate-900 font-bold px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas {{ $action['icon'] ?? 'fa-plus' }}"></i> {{ $action['label'] }}
                </a>
            @endforeach
        @endisset
        @isset($extraButtons)
        {!! $extraButtons !!}
        @endisset
    </div>
</div>
