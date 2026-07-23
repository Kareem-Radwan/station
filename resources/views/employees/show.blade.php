@extends('layouts.app')
@section('title', $employee->name)
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('employees.index') }}" class="text-slate-400 hover:text-white text-sm">الموظفون</a>
            <i class="fas fa-chevron-left text-slate-600 text-xs"></i>
            <span class="text-white font-bold">{{ $employee->name }}</span>
        </div>
        <span class="badge {{ $employee->is_active?'badge-green':'badge-gray' }}">{{ $employee->is_active?'نشط':'موقف' }}</span>
        @if($employee->position)<span class="badge badge-blue mr-2">{{ $employee->position }}</span>@endif
    </div>
    <div class="flex gap-3">
        <a href="{{ route('attendance.create') }}?employee_id={{ $employee->id }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-clock"></i> تسجيل حضور</a>
        <a href="{{ route('employees.edit',$employee) }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-edit"></i> تعديل</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card p-6 space-y-3 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i class="fas fa-user-tie text-amber-400 ml-2"></i>البيانات الشخصية</h3>
        @foreach([['الهاتف',$employee->phone??'-'],['رقم الهوية',$employee->national_id??'-'],['تاريخ التوظيف',$employee->hire_date?->format('d/m/Y')??'-']] as [$l,$v])
        <div class="flex justify-between"><span class="text-slate-400">{{ $l }}</span><span class="text-white">{{ $v }}</span></div>
        @endforeach
        @if($employee->notes)<div class="bg-slate-800/50 rounded-lg p-3 text-slate-300 mt-2">{{ $employee->notes }}</div>@endif
    </div>

    <div class="card p-6 space-y-3 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i class="fas fa-coins text-blue-400 ml-2"></i>الراتب</h3>
        <div class="flex justify-between"><span class="text-slate-400">الراتب الأساسي</span><span class="text-amber-400 font-bold text-xl">{{ number_format($employee->base_salary,0) }}</span></div>
        <div class="flex justify-between"><span class="text-slate-400">معدل الإضافي</span><span class="text-white">{{ $employee->overtime_rate ? number_format($employee->overtime_rate,2).' جنية/ساعة' : '-' }}</span></div>
    </div>

    <div class="card p-6 space-y-3 text-sm">
        <h3 class="text-white font-bold border-b border-slate-700 pb-3"><i class="fas fa-calendar-check text-green-400 ml-2"></i>الحضور هذا الشهر</h3>
        <div class="text-center py-2">
            <p class="text-4xl font-bold text-green-400">{{ $employee->attendance->count() }}</p>
            <p class="text-slate-400 text-xs mt-1">يوم حضور</p>
        </div>
        <a href="{{ route('attendance.index') }}?employee_id={{ $employee->id }}" class="block text-center text-xs text-blue-400 hover:text-blue-300">عرض كل الحضور ←</a>
    </div>
</div>

{{-- Borrows Section --}}
<div class="card overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
        <h3 class="text-white font-bold"><i class="fas fa-hand-holding-usd text-yellow-400 ml-2"></i>السلف</h3>
        <button onclick="document.getElementById('borrowModal').classList.remove('hidden')" class="btn-primary text-white px-4 py-2 rounded-lg text-sm">
            <i class="fas fa-plus ml-1"></i> إضافة سلفة
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-800/50 border-b border-slate-700">
                @foreach(['تاريخ السلفة','المبلغ','المسدد','المتبقي','السبب','الحالة','إجراءات'] as $h)
                <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($borrows as $borrow)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $borrow->borrow_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-amber-400 font-bold">{{ number_format($borrow->amount, 2) }}</td>
                    <td class="px-4 py-3 text-green-400">{{ number_format($borrow->paid_amount, 2) }}</td>
                    <td class="px-4 py-3 text-red-400 font-bold">{{ number_format($borrow->remaining_amount, 2) }}</td>
                    <td class="px-4 py-3 text-slate-300">{{ $borrow->reason ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $borrow->status === 'active' ? 'badge-yellow' : 'badge-green' }}">
                            {{ $borrow->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($borrow->status === 'active' && $borrow->deductions->isEmpty())
                        <form action="{{ route('employees.borrows.destroy', [$employee, $borrow]) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('هل تريد حذف هذه السلفة؟ سيتم إرجاع المبلغ للخزينة')" 
                                class="text-red-400 hover:text-red-300 text-xs">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">لا توجد سلف</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Borrow Modal -->
<div id="borrowModal" class="hidden fixed inset-0 bg-slate-900/70 flex items-center justify-center z-50">
    <div class="bg-slate-800 rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-bold text-lg">إضافة سلفة جديدة</h3>
            <button onclick="document.getElementById('borrowModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('employees.borrows.store', $employee) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-slate-300 text-sm mb-2">المبلغ <span class="text-red-400">*</span></label>
                <input type="number" name="amount" step="0.01" min="0.01" required 
                    class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-slate-300 text-sm mb-2">تاريخ السلفة <span class="text-red-400">*</span></label>
                <input type="date" name="borrow_date" required value="{{ date('Y-m-d') }}"
                    class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-slate-300 text-sm mb-2">السبب</label>
                <textarea name="reason" rows="3" 
                    class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-save ml-1"></i> حفظ
                </button>
                <button type="button" onclick="document.getElementById('borrowModal').classList.add('hidden')" 
                    class="bg-slate-700 hover:bg-slate-600 flex-1 text-white px-4 py-2 rounded-lg">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Attendance Table --}}
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700"><h3 class="text-white font-bold">آخر سجلات الحضور</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-800/50 border-b border-slate-700">
                @foreach(['التاريخ','الدخول','الخروج','ساعات العمل','الإضافي','الخصم'] as $h)
                <th class="px-4 py-3 text-right text-slate-400 font-medium">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($employee->attendance->take(15) as $a)
                <tr class="table-row">
                    <td class="px-4 py-3 text-slate-300">{{ $a->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-green-400">{{ $a->time_in }}</td>
                    <td class="px-4 py-3 text-red-400">{{ $a->time_out ?? '-' }}</td>
                    <td class="px-4 py-3 text-white">{{ $a->hours_worked ? number_format($a->hours_worked,1) : '-' }}</td>
                    <td class="px-4 py-3 text-amber-400">{{ $a->overtime_hours ? number_format($a->overtime_hours,1) : '-' }}</td>
                    <td class="px-4 py-3 text-red-400">{{ $a->deduction ? number_format($a->deduction,0) : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">لا توجد سجلات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
