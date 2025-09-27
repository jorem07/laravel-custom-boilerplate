<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Silber\Bouncer\Database\Ability;

class Role extends Model
{
    use HasFactory, SearchGenerator;

    protected $fillable = [
        'name',
        'guard_name'
    ];

    public function abilities()
    {
        return $this->belongsToMany(Ability::class, 'permissions', 'entity_id');
    }

}
