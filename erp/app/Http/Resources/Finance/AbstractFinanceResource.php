<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class AbstractFinanceResource extends JsonResource
{
    public function toArray($request): array
    {
        if (is_array($this->resource) && isset($this->resource['data'])) {
            $collection = collect($this->resource['data']);
            return [
                'status'  => $this->resource['status'] ?? true,
                'message' => $this->resource['message'] ?? '',
                'code'    => $this->resource['code'] ?? 200,
                'data'    => $collection->map(fn($item) => $this->formatItem($item)),
                'meta'    => $this->resource['meta'] ?? null,
            ];
        }

        if ($this->resource instanceof Collection) {
            return [
                'data' => $this->resource->map(fn($item) => $this->formatItem($item)),
                'meta' => [
                    'totalItems'   => $this->resource->count(),
                    'itemsPerPage' => 'all',
                    'totalPages'   => 1,
                    'currentPage'  => 1,
                ],
            ];
        }

        return $this->formatItem($this->resource);
    }
}
