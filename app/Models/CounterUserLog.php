<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CounterUserLog extends Model
{
    use HasFactory, SoftDeletes, SearchGenerator;

    protected $fillable = [
        'user_id',
        'counter_id',
        'log_in',
        'log_out',
    ];

    protected $casts = [
        'log_in' => 'datetime',
        'log_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class, 'counter_id', 'id');
    }
}
