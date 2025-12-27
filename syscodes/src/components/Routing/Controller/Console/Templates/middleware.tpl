<?php

namespace {{ namespace }};

use Closure;
use Syscodes\Components\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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