<?php
namespace App\DTO\User;

use App\DTO\Role\RoleDTO;
use App\Models\User;

class UserDTO
{
    public User $user;
    public array $roles;
    public ?int $office_id = null;

    public function __construct(User $user, ?int $officeId = null)
    {
        $this->user = $user;
        if ($user->relationLoaded('roles') && $user->roles) {
            $this->roles = $user->roles->map(fn($x) => RoleDTO::fromModel($x))->toArray();
        } else {
            $this->roles = $user->roles()->get()->map(fn($x) => RoleDTO::fromModel($x))->toArray();
        }
        $this->office_id = $officeId ?? ($user->office_id ? (int) $user->office_id : null);
    }

    public function toArray(): array
    {
        $userData = $this->user->toArray();
        if ($this->office_id) {
            $userData['office_id'] = $this->office_id;
            $userData['office_ids'] = [$this->office_id];
        } else {
            $userData['office_id'] = null;
            $userData['office_ids'] = [];
        }
        return array_merge($userData, ['roles' => $this->roles]);
    }

    public static function fromModel(User $user, ?int $resolvedOfficeId = null): self
    {
        $officeId = $resolvedOfficeId ?? ($user->office_id ? (int) $user->office_id : null);

        if (!$officeId) {
            $counter = \App\Models\Counter::where('user_id', $user->id)->first();
            if ($counter) {
                if ($counter->office_service_id) {
                    $officeId = \App\Models\OfficeService::where('id', $counter->office_service_id)->value('office_id');
                }
                if (!$officeId && !empty($counter->service_ids)) {
                    $officeId = \App\Models\OfficeService::whereIn('id', $counter->service_ids)->value('office_id');
                }
            }
        }

        return new self($user, $officeId ? (int) $officeId : null);
    }

    public static function fromCollection($users): array
    {
        $userIds = $users->pluck('id')->filter()->toArray();
        $counters = !empty($userIds)
            ? \App\Models\Counter::whereIn('user_id', $userIds)->get(['id', 'user_id', 'office_service_id', 'service_ids'])
            : collect();

        $serviceIds = [];
        foreach ($counters as $c) {
            if ($c->office_service_id) $serviceIds[] = (int) $c->office_service_id;
            if (!empty($c->service_ids)) {
                foreach ($c->service_ids as $sId) $serviceIds[] = (int) $sId;
            }
        }
        $serviceIds = array_unique(array_filter($serviceIds));

        $serviceOfficeMap = !empty($serviceIds)
            ? \App\Models\OfficeService::whereIn('id', $serviceIds)->pluck('office_id', 'id')->toArray()
            : [];

        $userOfficesMap = [];
        foreach ($counters as $c) {
            $offId = null;
            if ($c->office_service_id && isset($serviceOfficeMap[$c->office_service_id])) {
                $offId = $serviceOfficeMap[$c->office_service_id];
            }
            if (!$offId && !empty($c->service_ids)) {
                foreach ($c->service_ids as $sId) {
                    if (isset($serviceOfficeMap[$sId])) {
                        $offId = $serviceOfficeMap[$sId];
                        break;
                    }
                }
            }
            if ($offId && $c->user_id) {
                $userOfficesMap[$c->user_id] = (int) $offId;
            }
        }

        return $users
            ->map(function (User $user) use ($userOfficesMap) {
                $resolvedOfficeId = $user->office_id ? (int) $user->office_id : ($userOfficesMap[$user->id] ?? null);
                return self::fromModel($user, $resolvedOfficeId)->toArray();
            })
            ->toArray();
    }
}
