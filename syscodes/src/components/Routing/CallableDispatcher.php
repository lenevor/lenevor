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

namespace Syscodes\Components\Routing;

use Syscodes\Components\Container\Container;
use Syscodes\Components\Routing\Concerns\DependencyResolver;
use Syscodes\Components\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use ReflectionFunction;

/**
 * Allows make a request to a given callable. 
 */
class CallableDispatcher implements CallableDispatcherContract
{
    use DependencyResolver;

    /**
     * The container instance.
     *
     * @var \Syscodes\Components\Container\Container
     */
    protected $container;

    /**
     * Create a new callable dispatcher instance.
     *
     * @param  \Syscodes\Components\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given callable.
     *
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(Route $route, $callable)
    {
        return $callable(...array_values($this->resolveParameters($route, $callable)));
    }

    /**
     * Resolve the parameters for the callable.
     *
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  callable  $callable
     * @return array
     */
    protected function resolveParameters(Route $route, $callable): array
    {
        return $this->resolveMethodDependencies($route->parametersWithoutNulls(), new ReflectionFunction($callable));
    }
}