<?php

namespace App\Models;

use App\Traits\RelationTrait;
use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * OfficeService
 *
 * This is the Eloquent model for the OfficeService entity.
 * You can override or extend this class to add custom logic, relationships, or scopes.
 */
class OfficeService extends Model
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
        'name',
        'code',
        'subtitle',
        'icon',
        'icon_color',
        'office_id',
        'office_service_category_id'
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
    protected $hidden = ['deleted_at'];

    // You can override or add methods here to customize model
    protected $excludedColumn = [];

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    public function office() : BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }

    public function office_service_category() : BelongsTo
    {
        return $this->belongsTo(OfficeServiceCategory::class, 'office_service_category_id', 'id');
    }

    public function counters() : HasMany
    {
        return $this->hasMany(Counter::class, 'office_service_id', 'id');
    }

    public function requirements() : HasMany
    {
        return $this->hasMany(OfficeServiceRequirement::class, 'office_service_id', 'id');
    }
}

