<?php

namespace App\DTO\Counter;

use App\Models\Counter;

class CounterDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $user_id,
        public ?int $office_service_id,
        public ?array $service_ids = null,
        public ?array $service_names = null,
        public ?int $office_id = null,
        public ?string $user_name = null,
        public ?string $office_service_name = null,
        public bool $is_logged_in = false,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'office_service_id' => $this->office_service_id,
            'service_ids' => $this->service_ids,
            'service_names' => $this->service_names,
            'office_id' => $this->office_id,
            'user_name' => $this->user_name,
            'office_service_name' => $this->office_service_name,
            'is_logged_in' => $this->is_logged_in,
        ];
    }

    public static function fromModel(Counter $counter, array $preloadedServicesMap = []): self
    {
        $userName = null;
        if ($counter->relationLoaded('user') && $counter->user) {
            $userName = trim("{$counter->user->first_name} {$counter->user->last_name}");
        } elseif ($counter->user_id) {
            $u = \App\Models\User::find($counter->user_id);
            if ($u) {
                $userName = trim("{$u->first_name} {$u->last_name}");
            }
        }

        $serviceIds = $counter->service_ids ?: ($counter->office_service_id ? [$counter->office_service_id] : []);

        $serviceNames = [];
        if (!empty($serviceIds)) {
            $servicesMap = !empty($preloadedServicesMap)
                ? $preloadedServicesMap
                : \App\Models\OfficeService::whereIn('id', $serviceIds)->pluck('name', 'id')->toArray();

            foreach ($serviceIds as $sId) {
                if (isset($servicesMap[$sId])) {
                    $serviceNames[] = $servicesMap[$sId];
                }
            }
        }

        $officeServiceName = !empty($serviceNames)
            ? implode(', ', $serviceNames)
            : ($counter->relationLoaded('office_service') ? $counter->office_service?->name : null);

        $officeId = null;
        if ($counter->relationLoaded('office_service') && $counter->office_service) {
            $officeId = $counter->office_service->office_id;
        }
        if (!$officeId && $counter->office_service_id) {
            $officeId = \App\Models\OfficeService::where('id', $counter->office_service_id)->value('office_id');
        }
        if (!$officeId && !empty($serviceIds)) {
            $officeId = \App\Models\OfficeService::whereIn('id', $serviceIds)->value('office_id');
        }

        $isLoggedIn = false;
        if ($counter->relationLoaded('counter_user_logs') && $counter->counter_user_logs) {
            $isLoggedIn = $counter->counter_user_logs->whereNull('log_out')->isNotEmpty();
        } else {
            $isLoggedIn = \App\Models\CounterUserLog::where('counter_id', $counter->id)->whereNull('log_out')->exists();
        }

        return new self(
            id: $counter->id,
            name: $counter->name,
            user_id: $counter->user_id,
            office_service_id: $counter->office_service_id,
            service_ids: $serviceIds,
            service_names: !empty($serviceNames) ? $serviceNames : null,
            office_id: $officeId ? (int) $officeId : null,
            user_name: $userName ?: null,
            office_service_name: $officeServiceName,
            is_logged_in: $isLoggedIn,
        );
    }

    public static function fromCollection($counters): array
    {
        $allServiceIds = [];
        foreach ($counters as $counter) {
            $sIds = $counter->service_ids ?: ($counter->office_service_id ? [$counter->office_service_id] : []);
            foreach ($sIds as $sId) {
                $allServiceIds[] = (int) $sId;
            }
        }
        $allServiceIds = array_unique(array_filter($allServiceIds));

        $servicesMap = !empty($allServiceIds)
            ? \App\Models\OfficeService::whereIn('id', $allServiceIds)->pluck('name', 'id')->toArray()
            : [];

        return $counters
            ->map(fn (Counter $counter) => self::fromModel($counter, $servicesMap)->toArray())
            ->toArray();
    }
}
