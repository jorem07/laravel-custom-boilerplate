<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes, SearchGenerator;

    protected $fillable = [
        'title',
        'description',
        'announcement_status_id',
        'type',
        'icon',
        'icon_color',
        'icon_bg_color',
        'scheduled_at',
        'expires_at',
        'created_by',
        'publish_schedule',
        'expire_schedule'
    ];

    protected $excludedColumn = [
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function office() : BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }

    public function status() : BelongsTo
    {
        return $this->belongsTo(AnnouncementStatus::class, 'announcement_status_id', 'id');
    }
}
