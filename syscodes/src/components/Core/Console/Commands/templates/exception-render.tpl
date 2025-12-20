<?php

namespace {{ namespace }};

use Exception;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Response;

class {{ class }} extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        //
    }
}
