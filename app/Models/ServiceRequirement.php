<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequirement extends Model
{
    use HasFactory, SearchGenerator;

    protected $fillable = [
        'office_service_id',
        'description',
        'sort_order',
    ];

    protected $excludedColumn = [];

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    public function office_service(): BelongsTo
    {
        return $this->belongsTo(OfficeService::class, 'office_service_id', 'id');
    }
}
