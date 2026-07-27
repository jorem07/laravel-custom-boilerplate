<?php

namespace App\Models;

use App\Traits\RelationTrait;
use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Queue
 *
 * This is the Eloquent model for the Queue entity.
 * You can override or extend this class to add custom logic, relationships, or scopes.
 */
class Queue extends Model
{
    // Use Laravel traits for factory, soft deletes, and custom search functionality
    use HasFactory, SoftDeletes, SearchGenerator;

    /**
     * The attributes that are mass assignable.
     * Add your fillable fields here.
     *
     * @var array
     */
    protected $fillable = [
        'queue_no',
        'uuid',
        'token',
        'client_name',
        'client_phone',
        'purpose',
        'priority',
        'priority_type',
        'is_favorite',
        'counter_id',
        'office_service_id',
        'user_id',
        'queue_status_id',
        'time_start',
        'time_end'
    ];

    /**
     * The relationships that should always be loaded.
     * Add related models to eager load by default.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The attributes that should be hidden for arrays and JSON.
     * Add any fields you want to exclude from model's array or JSON output.
     *
     * @var array
     */
    protected $hidden = [];

    protected $excludedColumn = [
        'token',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    // You can override or add methods here to customize model
    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    public function queue_status() : BelongsTo
    {
        return $this->belongsTo(QueueStatus::class, 'queue_status_id', 'id');
    }

    public function office_service(): BelongsTo
    {
        return $this->belongsTo(OfficeService::class, 'office_service_id', 'id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class, 'counter_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

