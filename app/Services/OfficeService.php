<?php

namespace App\Services;

use App\DTO\BaseDTO;
use App\DTO\Office\OfficeDTO;
use App\Models\OfficeService as OfficeServiceModel;
use App\Repositories\Contracts\OfficeRepositoryInterface;
use App\Traits\ServiceTrait;
use Illuminate\Support\Facades\DB;

class OfficeService extends BaseService
{
    use ServiceTrait;

    protected OfficeRepositoryInterface $repository;

    public function __construct(OfficeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function delete($id, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
            $office = \App\Models\Office::withTrashed()->find($targetId);
            if ($office) {
                $serviceIds = OfficeServiceModel::withTrashed()->where('office_id', $targetId)->pluck('id');
                \App\Models\OfficeServiceRequirement::whereIn('office_service_id', $serviceIds)->forceDelete();
                \App\Models\Counter::whereIn('office_service_id', $serviceIds)->update(['office_service_id' => null]);
                \App\Models\User::where('office_id', $targetId)->update(['office_id' => null]);

                OfficeServiceModel::withTrashed()->where('office_id', $targetId)->forceDelete();
                \App\Models\OfficeServiceCategory::withTrashed()->where('office_id', $targetId)->forceDelete();
                $office->forceDelete();
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
