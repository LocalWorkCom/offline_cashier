<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    public function __construct(
        public Model $model
    ){}

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function prepareQuery(): Builder
    {
        return $this->model->query();
    }

    public function first(array $conditions = [])
    {
        return $this->prepareQuery()->where($conditions)->first();
    }

    public function update(array $conditions = [], array $data = [])
    {
        $model = $this->model->where($conditions)->firstOrFail();
        foreach ($data as $key => $value) {
            $model[$key] = $value;
        }
        $model->save();
        return $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function delete(int $id): void
    {
        $this->model->find($id)?->delete();
    }

    public function paginate(string $orderBy = 'desc', int $perPage = 10, array $filters = [])
    {
        return $this->model->orderBy('created_at', $orderBy)->where($filters)->paginate($perPage);
    }

    public function massDelete(array $conditions = []): void
    {
        $this->model->where($conditions)->delete();
    }
}
