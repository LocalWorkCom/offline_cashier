<?php

namespace App\Http\Resources;

use App\Traits\Paginatable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

abstract class AbstractResource extends JsonResource
{
    use Paginatable;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // Handle non-paginated collection case
        if ($this->resource instanceof Collection) {
            return [
                'items' =>
                    $this->resource->map(function ($item) {
                        return $this->formatItem($item);}
                    )->all()
            ];
        }

        // Handle single item case
        if (!($this->resource instanceof AbstractPaginator)) {
            return $this->formatItem($this->resource);
        }

        // Handle paginated collection case
        return $this->paginatedCollection();
    }
}
