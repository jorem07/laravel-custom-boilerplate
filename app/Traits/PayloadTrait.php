<?php

namespace App\Traits;

trait PayloadTrait
{
    public function payloadTaits() : array
    {
        return [
            'search'       => 'nullable',
            'full_search'  => 'nullable',
            'skip'         => 'nullable|numeric',
            'take'         => 'nullable|numeric'
        ];
    }
}
