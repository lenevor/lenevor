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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth\Middleware;

use Closure;
use Syscodes\Components\Contracts\Auth\Factory as Auth;
use Syscodes\Components\Auth\Exceptions\AuthenticationException;

/**
 * Determine if the user is logged using a given guards.
 */
class Authenticate
{
    /**
     * The authentication factory instance.
     * 
     * @var \Syscodes\Components\Contracts\Auth\Factory $auth
     */
    protected $auth;

    /**
     * Constructor. Create a new Authenticate class instance.
     * 
     * @param  \Syscodes\components\Contracts\Auth\Factory  $auth
     * 
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  $guards
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  array  guards
     * 
     * @return void
     * 
     * @throws \Syscodes\Components\Auth\Exceptions\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }
    
    /**
     * Handle an unauthenticated user.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  array  $guards
     * 
     * @return void
     * 
     * @throws \Syscodes\Components\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards): void
    {
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }
    
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return string|null
     */
    protected function redirectTo($request)
    {
        //
    }
}