<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    protected $fillable = ['week_start', 'week_end', 'created_by', 'status', 'notes'];

    protected $casts = [
        'week_start' => 'date',
        'week_end'   => 'date',
    ];

    public function entries()   { return $this->hasMany(WeeklyScheduleEntry::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function getStartDateAttribute()
    {
        return $this->week_start;
    }

    public function getEndDateAttribute()
    {
        return $this->week_end;
    }

    public function getWeekNumberAttribute()
    {
        return $this->week_start ? $this->week_start->weekOfYear : null;
    }

    public function getYearAttribute()
    {
        return $this->week_start ? $this->week_start->year : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'مسودة',
            'published' => 'منشور',
            'completed' => 'مكتمل',
            default     => $this->status,
        };
    }
}


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

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'معلق',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default     => $this->status,
        };
    }
}
