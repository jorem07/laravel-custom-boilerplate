<?php

namespace App\Models;

use App\Traits\RelationTrait;
use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Office
 *
 * This is the Eloquent model for the Office entity.
 * You can override or extend this class to add custom logic, relationships, or scopes.
 */
class Office extends Model
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
        'name'
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

    // You can override or add methods here to customize model
    protected $excludedColumn = [];

    public function officeServiceCategories()
    {
        return $this->hasMany(OfficeServiceCategory::class, 'office_id', 'id');
    }

    public function officeServices()
    {
        return $this->hasMany(OfficeService::class);
    }

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }
}

