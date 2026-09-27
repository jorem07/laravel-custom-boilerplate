<?php 

namespace App\Repositories\Contracts;


interface BaseRepositoryInterface
{
    public function query($payload, $searchable, $relation) : mixed;
    public function find(int $id): mixed;
    public function store(array $payload): mixed;
    public function update($model, array $payload): mixed;
    public function delete(int $id): bool;
}