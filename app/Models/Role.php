<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role as DatabaseRole;

class Role extends DatabaseRole
{
    use HasFactory, SearchGenerator;

    protected $fillable = [
        'name',
        'guard_name'
    ];

    protected $excludedColumn = [];

    // public function abilities()
    // {
    //     return $this->belongsToMany(Ability::class, 'permissions', 'entity_id');
    // }

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }
}
