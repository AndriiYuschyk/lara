<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company_id' => $this->id,
            'name' => $this->name,
            'edrpou' => $this->edrpou,
            'address' => $this->address,
            'versions_count' => $this->when(isset($this->versions_count), $this->versions_count),
            'created_at' => $this->created_at?->format('d.m.Y H:i:s'),
        ];
    }
}
