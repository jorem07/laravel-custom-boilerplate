<?php

namespace App\DTO\OfficeService;

use App\DTO\Office\OfficeDTO;
use App\Models\OfficeService;

class OfficeServiceDTO
{
    public OfficeService $officeService;
    public ?OfficeDTO $office;

    public function __construct(OfficeService $officeService)
    {
        $this->officeService = $officeService;
        $this->office = $officeService->office
            ? OfficeDTO::fromModel($officeService->office)
            : null;
    }

    public function toArray(): array
    {
        $data = $this->officeService->toArray();
        unset($data['office']);
        unset($data['deleted_at']);

        return array_merge($data, [
            'office_id'    => $this->office?->id,
            'offices_name' => $this->office?->name,
        ]);
    }

    public static function fromModel(OfficeService $officeService): self
    {
        return new self($officeService);
    }

    public static function fromCollection($officeServices): array
    {
        return $officeServices->map(fn ($item) => self::fromModel($item)->toArray())->all();
    }
}