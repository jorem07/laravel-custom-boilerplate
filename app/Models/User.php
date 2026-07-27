<?php

namespace App\Models;

use App\Traits\SearchGenerator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRolesAndAbilities, SearchGenerator, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fullname',
        'email',
        'phone_number',
        'assignment',
        'status',
        'is_online',
        'last_active_at',
        'birthday',
        'password',
        'email_verified_at',
        'allow_login',
        'created_by'
    ];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return $this->fullname ?? '';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $excludedColumn = [
        'password',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'email_verified_at',
        'allow_login',
        'email',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getExcludedColumn(): array
    {
        return $this->excludedColumn;
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by', 'id');
    }
}
