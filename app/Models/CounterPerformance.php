<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CounterPerformance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'counter_id',
        'office_service_id',
        'performance_date',
        'served_count',
        'total_service_minutes',
        'avg_time_minutes',
        'efficiency_percent',
    ];

    protected $casts = [
        'performance_date' => 'date',
        'avg_time_minutes' => 'decimal:2',
        'efficiency_percent' => 'decimal:2',
    ];

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class, 'counter_id', 'id');
    }

    public function office_service(): BelongsTo
    {
        return $this->belongsTo(OfficeService::class, 'office_service_id', 'id');
    }
}
