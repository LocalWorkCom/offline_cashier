<?php

namespace App\Repositories;

interface InsuranceLogRepositoryInterface
{
    public function paginate(string $sort = 'desc', int $perPage = 10, array $filters = []);
}
