<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyScheduleEntry extends Model
{
    protected $fillable = [
        'weekly_schedule_id', 'order_id', 'customer_id', 'site_location',
        'quantity_m3', 'delivery_date', 'delivery_time', 'engineer_notes', 'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'quantity_m3'   => 'decimal:3',
    ];

    public function schedule()  { return $this->belongsTo(WeeklySchedule::class, 'weekly_schedule_id'); }
    public function order()     { return $this->belongsTo(Order::class); }
    public function customer()  { return $this->belongsTo(Customer::class); }
}
