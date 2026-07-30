@extends('layouts.app')
@section('title', 'المرتجعات')
@section('content')

    @include('partials.page-header', [
        'title' => 'التقارير والإحصائيات',
        'icon' => 'fa-chart-bar',
    ])

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @php
            $reports = [
                [
                    'تقرير أرصدة العملاء',
                    'fa-users',
                    'reports.customer-balance',
                    'blue',
                    'يعرض رصيد كل عميل وإجمالي الطلبات والمدفوعات',
                ],
                [
                    'تقرير أرصدة الموردين',
                    'fa-truck',
                    'reports.supplier-balance',
                    'green',
                    'يعرض رصيد كل مورد وإجمالي المشتريات والمدفوعات',
                ],
                ['تقرير المخزون', 'fa-boxes', 'reports.inventory', 'orange', 'يعرض حالة كل مادة في المخزون'],
                ['تقرير الخزينة', 'fa-vault', 'reports.treasury', 'amber', 'يعرض حركات الخزينة والرصيد الحالي'],
                ['تقرير المصروفات', 'fa-receipt', 'reports.expenses', 'red', 'يعرض جميع المصروفات مع الفلترة حسب الفئة'],
                [
                    'تقرير الطلبات',
                    'fa-box',
                    'reports.orders',
                    'sky',
                    'يعرض جميع الطلبات مع الفلاتر والإحصائيات',
                ],
                [
                    'تقرير تكاليف المعدات',
                    'fa-cog',
                    'reports.equipment',
                    'purple',
                    'يعرض تكاليف الوقود والصيانة لكل معدة',
                ],
                ['تقرير الرواتب', 'fa-money-bill-wave', 'reports.payroll', 'cyan', 'يعرض رواتب الموظفين للشهر المحدد'],
                [
                    'تقرير الديون والآجل',
                    'fa-calendar-check',
                    'reports.credits',
                    'red',
                    'يعرض جميع الديون المستحقة والمتأخرة',
                ],
                [
                    'الربح الشهري',
                    'fa-chart-line',
                    'reports.monthly-profit',
                    'emerald',
                    'يعرض الإيرادات والمصروفات وصافي الربح للشهر',
                ],
                [
                    'الربح السنوي',
                    'fa-chart-area',
                    'reports.annual-profit',
                    'teal',
                    'يعرض الربح الشهري المفصّل لكل شهر في السنة',
                ],
                [
                    'تقرير المساهمين',
                    'fa-handshake',
                    'reports.contributor-balance',
                    'amber',
                    'يعرض كشف حساب المساهم والمدفوعات والمتبقي',
                ],
                [
                    'تقرير ورديات السيارات',
                    'fa-car',
                    'reports.rental-shifts',
                    'indigo',
                    'يعرض جميع ورديات السيارات المستأجرة بالتفصيل',
                ],
                [
                    'تقرير الجداول الأسبوعية',
                    'fa-calendar-week',
                    'reports.schedules',
                    'teal',
                    'يعرض الجداول الأسبوعية مع تفاصيل كل إدخال وكميات التوصيل',
                ],
                [
                    'التقرير العام',
                    'fa-chart-pie',
                    'reports.general',
                    'rose',
                    'ملخص شامل لجميع التقارير — مالي، عملاء، مخزون، موردون، رواتب، ديون',
                ],
                [
                    'تقرير المحطات المجاورة',
                    'fa-industry',
                    'reports.neighboring-stations',
                    'violet',
                    'يعرض جميع المعاملات مع المحطات المجاورة والأرصدة المستحقة',
                ],
                [
                    'ميزان المراجعة',
                    'fa-scale-balanced',
                    'reports.trial-balance',
                    'slate',
                    'يصدر ميزان مراجعة شامل بجميع الحسابات — مدين ودائن — بصيغة Excel',
                ],
            ];
            
            $colorMap = [
                'blue'   => '#3b82f6',
                'green'  => '#10b981',
                'orange' => '#f97316',
                'amber'  => '#f59e0b',
                'purple' => '#8b5cf6',
                'cyan'   => '#06b6d4',
                'red'    => '#ef4444',
                'emerald'=> '#34d399',
                'teal'   => '#14b8a6',
                'indigo' => '#6366f1',
                'sky'    => '#0ea5e9',
                'rose'   => '#f43f5e',
                'violet' => '#7c3aed',
                'slate'  => '#64748b',
            ];
        @endphp

        @foreach ($reports as [$title, $icon, $route, $color, $desc])
            <a href="{{ route($route) }}"
                class="card p-5 rounded-2xl group hover:border-amber-500/40 border border-slate-700/30 transition-all hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-0.5">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                        style="background: {{ $colorMap[$color] }}22; border: 1px solid {{ $colorMap[$color] }}44">
                        <i class="fas {{ $icon }} text-lg" style="color: {{ $colorMap[$color] }}"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-white font-bold text-sm group-hover:text-amber-400 transition">{{ $title }}
                        </h3>
                    </div>
                    <i class="fas fa-arrow-left text-slate-600 group-hover:text-amber-400 transition"></i>
                </div>
                <p class="text-slate-500 text-xs">{{ $desc }}</p>
            </a>
        @endforeach
    </div>
@endsection

