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

namespace Syscodes\Components\Routing\Middleware;

use Closure;
use Syscodes\Components\Contracts\Routing\Routable;
use Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException;

/**
 * Allows the substitute bindings.
 */
class SubstituteBindings
{
    /**
     * The router instance.
     *
     * @var \Syscodes\Components\Contracts\Routing\Routable
     */
    protected $router;

    /**
     * Create a new bindings substitutor.
     *
     * @param  \Syscodes\Components\Contracts\Routing\Routable  $router
     * @return void
     */
    public function __construct(Routable $router)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Syscodes\Components\Database\Erostrine\Exceptions\ModelNotFoundException
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route();

        try {
            $this->router->substituteBindings($route);
        } catch (ModelNotFoundException $exception) {
            if ($route->getMissing()) {
                return $route->getMissing()($request, $exception);
            }

            throw $exception;
        }

        return $next($request);
    }
}