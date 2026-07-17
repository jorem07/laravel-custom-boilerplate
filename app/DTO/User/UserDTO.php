<?php
namespace App\DTO\User;

use App\DTO\Role\RoleDTO;
use App\Models\User;

class UserDTO
{
    public User $user;
    public array $roles;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->roles = $user->roles->map(fn($x) => RoleDTO::fromModel($x))->toArray() ?? [];
    }

    public function toArray(): array
    {
        return array_merge($this->user->toArray(), ['roles' => $this->roles]);
    }

    public static function fromModel(User $user): self
    {
        return new self($user);
    }

    public static function fromCollection($users): array
    {
        return $users
            ->map(fn (User $user) => self::fromModel($user)->toArray())
            ->toArray();
    }
}
