<?php

namespace App\Services;

use App\DTO\Announcement\AnnouncementDTO;
use App\Repositories\Contracts\AnnouncementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AnnouncementService extends BaseService
{
    protected AnnouncementRepositoryInterface $announcement;

    public function __construct(AnnouncementRepositoryInterface $announcement)
    {
        $this->announcement = $announcement;
    }

    public function index($payload, array $searchable = [], $relation = []): array
    {
        $take = $payload['show'] ?? 100;
        $page = $payload['page'] ?? 1;
        $skip = ($page > 1) ? ($take * ($page - 1)) : 0;
        $order = $payload['sort']['order'] ?? 'desc';
        $sort = $payload['sort']['column'] ?? 'id';

        $selected_relation = $this->format($relation);
        $searchable = $this->getSearchable('App\\Models\\Announcement', $relation);

        $data = $this->announcement->query($payload, $searchable, $selected_relation);

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
            'body' => AnnouncementDTO::fromCollection($list),
            'searchable' => $searchable
        ];
    }

    public function show($id, $payload = [], $relation = []): array
    {
        $data = collect([$this->announcement->find($id)]);

        $message = 'Showing Data.';
        if (!$data) {
            $message = 'No result found.';
        }

        return [
            'message' => $message,
            'body' => AnnouncementDTO::fromCollection($data)
        ];
    }

    public function store($payload, $relation = []): array
    {
        DB::beginTransaction();
        try {
            $announcement = $this->announcement->store($payload);
            $data = collect([$announcement]);

            DB::commit();
            return [
                'message' => 'Announcement created successfully.',
                'body' => AnnouncementDTO::fromCollection($data)
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
            $this->announcement->delete($payload);
            DB::commit();
            return [
                'message' => 'Announcement deleted successfully.',
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
            $announcement = $this->announcement->find($id);
            $this->announcement->update($announcement, $payload);
            $data = collect([$announcement]);

            DB::commit();
            return [
                'message' => 'Announcement updated successfully.',
                'body' => AnnouncementDTO::fromCollection($data)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
