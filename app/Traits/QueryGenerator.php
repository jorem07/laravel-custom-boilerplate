<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

trait QueryGenerator
{
    public function getJsonResponse(array $data): JsonResponse
    {
        $status = $data['status'] ?? 200;
        return response()->json([
            'message' => $data['message'] ?? null,
            'error' => $data['error'] ?? null,
            'details' => [
                'current_page' => $data['current_page'] ?? null,
                'from' => isset($data['skip']) ? $data['skip'] + 1 : null,
                'to' => $data['to'] ?? null,
                'last_page' => $data['last_page'] ?? null,
                'skip' => $data['skip'] ?? null,
                'take' => $data['take'] ?? null,
                'total' => $data['total'] ?? null,
            ],
            'headers' => $data['headers'] ?? null,
            'body' => $data['body'] ?? null,
            'searchable' => $data['searchable'] ?? null,
            'others' => $data['others'] ?? null,
        ], $status);
    }

    public function index($payload, array $searchable = []) : array
    {
        $search = $payload['search'] ?? [];
        $skip = $payload['skip'] ?? null;
        $take = $payload['take'] ?? null;
        $full_search = $payload['full_search'] ?? null;

        $data = $this->model->newQuery();
        
        $data->searchColumns($search);
        $data->fullSearch($full_search, $searchable);

        $total = $data->count();
        $list = $data->skip($skip)->take($take)->get();
        
        return [
            'message' => 'These are the results.',
            'error' => null,
            'current_page' => $take > 0 ? intval($skip / $take) + 1 : 1,
            'from' => $skip + 1,
            'to' => min(($skip + $take), $total),
            'skip' => $skip,
            'take' => $take,
            'total' => $total,
            'body' => $list,
            'searchable' => !empty($searchable) ? $searchable : null
        ];
    }

    public function show($id, $payload) : array
    {
        $data = $this->model->find($id);

        return [
            'message' => 'Showing Data.',
            'body' => $data
        ];
    }

    public function store($payload) : array
    {
        $data = $this->model->create($payload);

        return $this->show($data->id, $payload);
    }

    public function update($id, $payload) : array
    {
        $data = $this->model->find($id);

        $data->update($payload);

        return $this->show($id, $payload);
    }

    public function delete($id)
    {
        $data = $this->model->where('id', $id)->update(['deleted_at' => Carbon::now()]);

        return ['message' => 'Data has successfully deleted.'];
    }
}
