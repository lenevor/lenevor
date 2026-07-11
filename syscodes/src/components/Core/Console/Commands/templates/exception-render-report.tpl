<?php

namespace {{ namespace }};

use Exception;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Response;

class {{ class }} extends Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @return \Syscodes\Components\Http\Response
     */
    public function render(Request $request): Response
    {
        //
    }
}
