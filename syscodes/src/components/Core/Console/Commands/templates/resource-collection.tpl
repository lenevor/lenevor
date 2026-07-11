<?php

namespace {{ namespace }};

use Syscodes\Compoenents\Http\Request;
use Syscodes\Compoenents\Http\Resources\Json\ResourceCollection;

class {{ class }} extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Syscodes\Compoenents\Http\Request  $request
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}