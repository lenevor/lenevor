<?php

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Cookie\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Syscodes\Components\Contracts\Cookie\QueueingFactory as Cookie;

/**
 * Added cookie after of the request.
 */
class AddQueuedCookiesResponse
{
    /**
     * The cookie manager instance.
     * 
     * @var \Syscodes\Components\Contracts\Cookie\QueueingFactory $cookies
     */
    protected $cookies;
    
    /**
     * Constructor. Create a new CookieQueue instance.
     * 
     * @param  \Syscodes\Components\Contracts\Cookie\QueueingFactory  $cookies
     * 
     * @return void
     */
    public function __construct(Cookie $cookies)
    {
        $this->cookies = $cookies;
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next): Response
    {
        $response = $next($request);
        
        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }
        
        return $response;
    }
}