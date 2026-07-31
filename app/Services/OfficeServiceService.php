<?php

namespace App\Services;

use App\DTO\BaseDTO;
use App\DTO\OfficeService\OfficeServiceDTO;
use App\Models\OfficeServiceRequirement;
use App\Repositories\Contracts\OfficeServiceRepositoryInterface;
use App\Traits\ServiceTrait;
use Illuminate\Support\Facades\DB;

class OfficeServiceService extends BaseService
{
    use ServiceTrait;
    protected OfficeServiceRepositoryInterface $repository;

    public function __construct(OfficeServiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Store data.
     */
    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {

            $data = $this->repository->store($payload);

            if(isset($payload['requirements'])) $data->requirements()->createMany($payload['requirements']);

            $data = collect([$data]);

            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => BaseDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update specific data.
     *  @param int $id
     */
    public function update($id, $payload, $relation) : array
    {
        DB::beginTransaction();
        try {
            $data = $this->repository->find($id);

            $this->repository->update($data, $payload);

            if(isset($payload['requirements']))
            {
                $requirementIds = collect($payload['requirements'])->pluck('id')->filter();
                $officeService = OfficeServiceRequirement::where('office_service_id', $id);

                $excluded = (clone $officeService)->whereNotIn('id', $requirementIds);
                $excluded->forceDelete();
                foreach($payload['requirements'] as $requirement)
                {
                    OfficeServiceRequirement::updateOrCreate([
                        'id' => $requirement['id'] ?? null, 
                        'office_service_id' => $id
                    ], $requirement);
                }
            }

            $data = collect([$data]);

            DB::commit();
            return [
                'message' => 'Data updated successfully.',
                'body' => BaseDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
