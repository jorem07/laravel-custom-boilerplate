<?php

namespace App\Services;

use App\DTO\OfficeServiceCategory\OfficeServiceCategoryDTO;
use App\Repositories\Contracts\OfficeServiceCategoryRepositoryInterface;
use App\Traits\ServiceTrait;
use Illuminate\Support\Facades\DB;

class OfficeServiceCategoryService extends BaseService
{
    use ServiceTrait;
    
    protected OfficeServiceCategoryRepositoryInterface $repository;

    public function __construct(OfficeServiceCategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $data = $this->repository->store($payload);
            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => [OfficeServiceCategoryDTO::fromModel($data)->toArray()]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, $payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
            $data = $this->repository->find($targetId);
            if ($data) {
                $this->repository->update($data, $payload);
            }

            DB::commit();
            return [
                'message' => 'Data updated successfully.',
                'body' => [OfficeServiceCategoryDTO::fromModel($data)->toArray()]
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
            $category = \App\Models\OfficeServiceCategory::withTrashed()->find($targetId);
            if ($category) {
                \App\Models\OfficeService::withTrashed()->where('office_service_category_id', $targetId)->forceDelete();
                $category->forceDelete();
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

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 100;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;

        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\OfficeServiceCategory', $relation);

        $data = $this->repository->query($payload, $searchable, $selected_relation);

        if (!empty($payload['office_id'])) {
            $data->where('office_id', (int) $payload['office_id']);
        }

        $total = $data->count();

        $list = $data->skip($skip)
            ->take($take)
            ->when(isset($payload['sort']), function ($q) use ($sort, $order) {
                $q->orderBy($sort, $order);
            })
            ->get();

        return [
            'message' => 'These are the results.',
            'error' => null,
            'current_page' => $take > 0 ? intval($skip / $take) + 1 : 1,
            'from' => $skip + 1,
            'to' => min(($skip + $take), $total),
            'last_page' => ($take > 0) ? ceil($total / $take) : 1,
            'skip' => $skip,
            'take' => $take,
            'total' => $total,
            'body' => OfficeServiceCategoryDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    // public function show($id, $payload = [], $relation = []): array
    // {
    //     $data = collect([$this->officeServiceCategory->find($id)]);

    //     $message = 'Showing Data.';
    //     if (!$data) {
    //         $message = 'No result found.';
    //     }

    //     return [
    //         'message' => $message,
    //         'body' => OfficeServiceCategoryDTO::fromCollection($data)
    //     ];
    // }

    // public function store($payload, $relation = []): array
    // {
    //     DB::beginTransaction();
    //     try {

    //         $officeServiceCategory = $this->officeServiceCategory->store($payload);

    //         $data = collect([$officeServiceCategory]);

    //         DB::commit();
    //         return [
    //             'message' => 'Data created successfully.',
    //             'body' => OfficeServiceCategoryDTO::fromCollection($data)
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }

    // public function delete($payload, $relation = []): array
    // {
    //     DB::beginTransaction();
    //     try {
    //         $this->officeServiceCategory->delete($payload);
    //         DB::commit();
    //         return [
    //             'message' => 'Data deleted successfully.',
    //             'body' => null
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // } 

    // public function update($id, $payload, $relation) : array
    // {
    //     DB::beginTransaction();
    //     try {
    //         $officeServiceCategory = $this->officeServiceCategory->find($id);

    //         $this->officeServiceCategory->update($officeServiceCategory, $payload);

    //         $data = collect([$officeServiceCategory]);

    //         DB::commit();
    //         return [
    //             'message' => 'Data updated successfully.',
    //             'body' => OfficeServiceCategoryDTO::fromCollection($data)
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }
}
