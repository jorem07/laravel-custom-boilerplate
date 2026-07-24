<?php

namespace App\Traits;

use App\DTO\BaseDTO;
use Illuminate\Support\Facades\DB;

trait ServiceTrait
{
    /**
     * Get all data.
     */
    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;


        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable(self::getModel(), $relation);

        $data = $this->repository->query($payload, $searchable, $selected_relation);

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
            'body' => BaseDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    /**
     * Get specific data.
     * @param int $id
     */
    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->repository->find($id)]);

        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => BaseDTO::fromCollection($data)
        ];
    }

    /**
     * Store data.
     */
    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {

            $sample = $this->repository->store($payload);

            $data = collect([$sample]);

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
     * Delete specific data.
     * @param int $id
     */
    public function delete($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $this->repository->delete($payload);
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

    /**
     * Update specific data.
     *  @param int $id
     */
    public function update($id, $payload, $relation) : array
    {
        DB::beginTransaction();
        try {
            $sample = $this->repository->find($id);

            $this->repository->update($sample, $payload);

            $data = collect([$sample]);

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

    private function getModel() : string
    {
        $repo = (new \ReflectionClass($this))->getProperty('repository')->getValue($this);
        $model = class_basename((new \ReflectionClass($repo))->getProperty('model')->getValue($repo));

        return "App\\Models\\{$model}";
    }
}
