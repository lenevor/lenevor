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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Controller;

use Syscodes\Routing\Route;
use Syscodes\Contracts\Container\Container;
use Syscodes\Routing\Concerns\RouteDependencyResolver;
use Syscodes\Controller\Contracts\ControllerDispatcher as ControllerDispatcherContract;

/**
 * Dispatch requests when called a given controller and method.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ControllerDispatcher implements ControllerDispatcherContract
{
    use RouteDependencyResolver;

    /**
     * The container instance.
     * 
     * @var \Syscodes\Contracts\Container\Container  $container
     */
    protected $container;

    /**
     * Constructor. The ControllerDispatcher class instance.
     * 
     * @param  \Syscodes\Contracts\Container\Container  $container
     * 
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given controller and method.
     * 
     * @param  \Syscodes\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * 
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method)
    {
        $parameters = $this->resolveObjectMethodDependencies(
            $route->parametersWithouNulls(), $controller, $method
        );
        
        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $parameters);
        }
        
        return $controller->{$method}(...array_values($parameters));
    }
}