<?php

namespace {{ namespace }};

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Syscodes\Components\Http\Request;

class {{ class }}
{
    /**
     * Handle an incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}