<?php

namespace App\Repositories;

interface InsuranceRepositoryInterface
{
    public function paginate(string $sort = 'desc', int $perPage = 10, array $filters = []);

    public function create(array $data);

    public function update(array $conditions, array $data);

    public function delete(int $id);

    public function all();
}
