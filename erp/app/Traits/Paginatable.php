<?php

namespace App\Traits;

trait Paginatable
{
    /**
     * Format paginated collection
     */
    protected function paginatedCollection(): array
    {
        return [
            'items' => $this->resource->getCollection()->map(function ($item) {
                return $this->formatItem($item);
            }),
            'meta' => [
                'pagination' => [
                    'total' => $this->resource->total(),
                    'count' => $this->resource->count(),
                    'per_page' => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'total_pages' => $this->resource->lastPage()
                ],
                'links' => [
                    'self' => $this->resource->url($this->resource->currentPage()),
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'next' => $this->resource->nextPageUrl(),
                    'prev' => $this->resource->previousPageUrl(),
                ],
            ]
        ];
    }
}
