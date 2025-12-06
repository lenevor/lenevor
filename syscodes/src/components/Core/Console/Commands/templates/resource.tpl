<?php

namespace {{ namespace }};

use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Resources\Json\JsonResource;

class {{ class }} extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}