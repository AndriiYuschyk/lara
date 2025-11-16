<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CompanyCollection extends ResourceCollection
{
    public $collects = CompanyResource::class;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => CompanyResource::collection($this->collection),
            'meta' => [
                'page_number' => $this->currentPage(),
                'page_size' => $this->perPage(),
                'total_count' => $this->total(),
                'total_pages' => $this->lastPage(),
            ],
        ];
    }
}
