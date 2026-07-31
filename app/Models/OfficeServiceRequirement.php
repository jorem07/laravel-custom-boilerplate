<?php

namespace App\Models;

use App\Traits\RelationTrait;
use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * OfficeServiceRequirement
 *
 * This is the Eloquent model for the OfficeServiceRequirement entity.
 * You can override or extend this class to add custom logic, relationships, or scopes.
 */
class OfficeServiceRequirement extends Model
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
        'list',
        'office_service_id'
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

    protected $excludedColumn = [];

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    // You can override or add methods here to customize model
}

