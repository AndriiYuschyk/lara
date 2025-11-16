<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company_id' => $this->company_id,
            'version' => $this->version,
            'name' => $this->name,
            'edrpou' => $this->company->edrpou,
            'address' => $this->address,
            'created_at' => $this->created_at?->format('d.m.Y H:i:s'),
        ];
    }
}
