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

use Syscodes\Components\Routing\Route;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Routing\Concerns\DependencyResolver;
use Syscodes\Components\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;

/**
 * Dispatch requests when called a given controller and method.
 */
class ControllerDispatcher implements ControllerDispatcherContract
{
    use DependencyResolver;

    /**
     * The container instance.
     * 
     * @var \Syscodes\Contracts\Container\Container  $container
     */
    protected $container;

    /**
     * Constructor. The ControllerDispatcher class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
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
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * 
     * @return mixed
     */
    public function dispatch(Route $route, mixed $controller, string $method): mixed
    {
        $parameters = $this->resolveObjectMethodDependencies(
            $route->parametersWithouNulls(), $controller, $method
        );
        
        if (method_exists($controller, 'callAction')) {
            return $controller->callAction($method, $parameters);
        }
        
        return $controller->{$method}(...array_values($parameters));
    }

    /**
     * Get the middleware for the controller instance.
     * 
     * @param  \Syscodes\Components\Routing\Controller  $controller
     * @param  string  $method
     * 
     * @return array
     */
    public function getMiddleware($controller, string $method): array
    {
        if ( ! method_exists($controller, 'getMiddleware'))
        {
            return [];
        }

        $middleware = $controller->getMiddleware();

        return collect($middleware)
                    ->reject(fn ($data) => $this->methodExcludedByOptions($method, $data['options']))
                    ->pluck('middleware')
                    ->all();
    }

    /**
     * Determine if the given options exclude a particular method.
     * 
     * @param  string  $method
     * @param  array  $options
     * 
     * @return bool
     */
    protected function methodExcludedByOptions($method, array $options): bool
    {
        return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
            ( ! empty($options['except']) && in_array($method, (array) $options['except']));
    }
}