<?php

namespace App\Repositories;

interface InsuranceEmployeeLogRepositoryInterface
{
    public function paginate(string $sort = 'desc', int $perPage = 10, array $filters = []);
}
