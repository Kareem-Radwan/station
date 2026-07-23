<?php

namespace App\Http\Controllers;

use App\Models\WeeklySchedule;
use App\Models\WeeklyScheduleEntry;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class WeeklyScheduleController extends Controller
{
    public function index()
    {
        $schedules = WeeklySchedule::with('entries.customer')
            ->withCount('entries')
            ->latest('week_start')->paginate(15);
        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        $nextMonday = now()->next('Monday');
        $nextSunday = $nextMonday->copy()->addDays(6);
        return view('schedules.create', compact('nextMonday','nextSunday'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'week_start' => 'required|date',
            'week_end'   => 'required|date|after_or_equal:week_start',
            'notes'      => 'nullable|string',
        ]);

        $schedule = WeeklySchedule::create([
            'week_start' => $request->week_start,
            'week_end'   => $request->week_end,
            'status'     => 'draft',
            'notes'      => $request->notes,
        ]);

        return redirect()->route('schedules.show', $schedule)->with('success', 'تم إنشاء الجدول الأسبوعي');
    }

    public function show(WeeklySchedule $schedule)
    {
        $schedule->load(['entries.customer', 'entries.order']);
        $customers = Customer::active()->orderBy('name')->get();
        $orders    = Order::whereIn('status', ['pending', 'scheduled'])
            ->whereDoesntHave('scheduleEntry')
            ->with('customer')
            ->get();
        return view('schedules.show', compact('schedule','customers','orders'));
    }

    public function edit(WeeklySchedule $schedule)
    {
        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, WeeklySchedule $schedule)
    {
        $rules = [
            'status' => 'required|in:draft,published,completed',
            'notes'  => 'nullable|string',
        ];

        if ($request->hasAny(['week_start', 'week_end'])) {
            $rules['week_start'] = 'required|date';
            $rules['week_end']   = 'required|date|after_or_equal:week_start';
        }

        $request->validate($rules);
        $schedule->update($request->only(['week_start', 'week_end', 'status', 'notes']));
        return redirect()->route('schedules.show', $schedule)->with('success', 'تم تحديث الجدول');
    }

    public function destroy(WeeklySchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedules.index')->with('success', 'تم حذف الجدول');
    }

    public function addEntry(Request $request, WeeklySchedule $schedule)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'order_id'       => 'nullable|exists:orders,id',
            'site_location'  => 'required|string|max:255',
            'quantity_m3'    => 'required|numeric|min:0.001',
            'delivery_date'  => 'required|date',
            'delivery_time'  => 'nullable',
            'engineer_notes' => 'nullable|string',
        ]);

        $deliveryDate = \Carbon\Carbon::parse($request->delivery_date);
        if ($deliveryDate->lt($schedule->week_start) || $deliveryDate->gt($schedule->week_end)) {
            return back()->withErrors([
                'delivery_date' => 'تاريخ التوصيل يجب أن يكون ضمن نطاق الأسبوع الممتد من ' . $schedule->week_start->format('d/m/Y') . ' إلى ' . $schedule->week_end->format('d/m/Y')
            ])->withInput();
        }

        $entry = WeeklyScheduleEntry::create(array_merge($request->all(), ['weekly_schedule_id' => $schedule->id]));

        if ($entry->order_id) {
            $order = Order::find($entry->order_id);
            if ($order) {
                $order->update(['status' => 'scheduled']);
            }
        }

        return back()->with('success', 'تم إضافة إدخال للجدول');
    }

    public function deleteEntry(WeeklyScheduleEntry $entry)
    {
        $scheduleId = $entry->weekly_schedule_id;
        
        if ($entry->order_id) {
            $order = Order::find($entry->order_id);
            if ($order) {
                $order->update(['status' => 'pending']);
            }
        }

        $entry->delete();
        return redirect()->route('schedules.show', $scheduleId)->with('success', 'تم حذف الإدخال');
    }
}
