<?php

namespace App\Services;

use App\DTO\OfficeService\OfficeServiceDTO;
use App\Repositories\Contracts\OfficeServiceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OfficeServiceService extends BaseService
{

    protected OfficeServiceRepositoryInterface $officeService;

    public function __construct(OfficeServiceRepositoryInterface $officeService)
    {
        $this->officeService = $officeService;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;


        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\OfficeService', $relation);

        $data = $this->officeService->query($payload, $searchable, $selected_relation);

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
            'body' => OfficeServiceDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->officeService->find($id)]);

        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => OfficeServiceDTO::fromCollection($data)
        ];
    }

    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {

            $officeService = $this->officeService->store($payload);

            $data = collect([$officeService]);

            DB::commit();
            return [
                'message' => 'Data created successfully.',
                'body' => OfficeServiceDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $this->officeService->delete($payload);
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

    public function update($id, $payload, $relation) : array
    {
        DB::beginTransaction();
        try {
            $officeService = $this->officeService->find($id);

            $this->officeService->update($officeService, $payload);

            $data = collect([$officeService]);

            DB::commit();
            return [
                'message' => 'Data updated successfully.',
                'body' => OfficeServiceDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
