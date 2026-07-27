<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Display extends Model
{
    use HasFactory, SoftDeletes, SearchGenerator;

    protected $fillable = [
        'name',
        'location',
        'status',
        'connection_status',
        'last_heartbeat_at',
        'office_id',
    ];

    protected $excludedColumn = [
        'deleted_at',
    ];

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_heartbeat_at' => 'datetime',
        ];
    }
}
