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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth\Middleware;

use Closure;
use Syscodes\Components\Contracts\Auth\Factory;

/**
 * Determine if the user is logged using a given guards basic.
 */
class AuthenticateWithBasicAuth
{
    /**
     * The guard factory instance.
     * 
     * @var \Syscodes\Components\Contracts\Auth\Factory $auth
     */
    protected $auth;
    
    /**
     * Constructor. Create a new  AuthenticateWithBasicAuth class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Factory  $auth
     * 
     * @return void
     */
    public function __construct(Factory $auth)
    {
        $this->auth = $auth;
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @param  string|null  $field
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\UnauthorizedHttpException
     */
    public function handle($request, Closure $next, $guard = null, $field = null)
    {
        $this->auth->guard($guard)->basic($field ?: 'email');
        
        return $next($request);
    }
}