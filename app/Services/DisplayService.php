<?php

namespace App\Services;

use App\DTO\Display\DisplayDTO;
use App\Repositories\Contracts\DisplayRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DisplayService extends BaseService
{
    protected DisplayRepositoryInterface $display;

    public function __construct(DisplayRepositoryInterface $display)
    {
        $this->display = $display;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 10;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? null;
        $sort = $payload['sort']['column'] ?? null;

        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\Display', $relation);

        $data = $this->display->query($payload, $searchable, $selected_relation);

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
            'body' => DisplayDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->display->find($id)]);

        return [
            'message' => 'Showing Data.',
            'body' => DisplayDTO::fromCollection($data)
        ];
    }

    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $display = $this->display->store($payload);
            $data = collect([$display]);

            DB::commit();
            return [
                'message' => 'Display created successfully.',
                'body' => DisplayDTO::fromCollection($data)
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
            $this->display->delete($payload);
            DB::commit();
            return [
                'message' => 'Display deleted successfully.',
                'body' => null
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
            $display = $this->display->find($id);
            $this->display->update($display, $payload);
            $data = collect([$display]);

            DB::commit();
            return [
                'message' => 'Display updated successfully.',
                'body' => DisplayDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
