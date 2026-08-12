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
            $requirements = $payload['requirements'] ?? [];
            unset($payload['requirements'], $payload['requirement']);

            $data = $this->repository->store($payload);

            if (!empty($requirements)) {
                $data->requirements()->createMany($requirements);
            }

            $data->load(['office', 'requirements']);
            $collection = collect([$data]);

            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => OfficeServiceDTO::fromCollection($collection)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update specific data.
     * @param int $id
     */
    public function update($id, $payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
            $data = $this->repository->find($targetId);

            $requirements = $payload['requirements'] ?? [];
            unset($payload['requirements'], $payload['requirement']);

            $this->repository->update($data, $payload);

            if (isset($requirements)) {
                $requirementIds = collect($requirements)->pluck('id')->filter();
                OfficeServiceRequirement::where('office_service_id', $targetId)
                    ->whereNotIn('id', $requirementIds)
                    ->delete();

                foreach ($requirements as $requirement) {
                    OfficeServiceRequirement::updateOrCreate([
                        'id' => $requirement['id'] ?? null,
                        'office_service_id' => $targetId,
                    ], [
                        'list' => $requirement['list'] ?? '',
                        'office_service_id' => $targetId,
                    ]);
                }
            }

            $data->load(['office', 'requirements']);
            $collection = collect([$data]);

            DB::commit();
            return [
                'message' => 'Data updated successfully.',
                'body' => OfficeServiceDTO::fromCollection($collection)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
            $service = \App\Models\OfficeService::withTrashed()->find($targetId);
            if ($service) {
                OfficeServiceRequirement::where('office_service_id', $targetId)->forceDelete();
                \App\Models\Counter::where('office_service_id', $targetId)->update(['office_service_id' => null]);
                $service->forceDelete();
            }
            DB::commit();
            return [
                'message' => 'Data deleted successfully.',
                'body' => null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
