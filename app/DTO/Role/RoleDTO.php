<?php
namespace App\DTO\Role;

use Silber\Bouncer\Database\Role;

class RoleDTO
{
    

    public function __construct(
        public int $id,
        public string $name,
        public ?string $title
    )
    {}

    public static function fromModel(Role $role) : self
    {
        return new self(
            id: $role->id,
            name: $role->name,
            title: $role->title
        );
    }

    public static function fromCollection($roles): array
    {
        return $roles
            ->map(fn (Role $role) => self::fromModel($role))
            ->toArray();
    }
}
