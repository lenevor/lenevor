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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing;

use BadMethodCallException;

/**
 * This class allows you to manage the middlewares, actions and parameters 
 * in each controller created by the user.
 */
abstract class Controller
{
    /**
     * The middleware registered on the controller.
     * 
     * @var array $middleware
     */
    protected $middleware = [];

    /**
     * Register middleware on the controller.
     * 
     * @param  \Closure|array|string  $middleware
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Routing\ControllerMiddlewareOptions
     */
    public function middleware($middleware, array $options = [])
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = [
                'middleware' => $m,
                'options' => &$options
            ];
        }

        return new ControllerMiddlewareOptions($options);
    }

    /**
     * Get the middleware assigned to the controller.
     * 
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Execute an action on the controller.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function callAction($method, $parameters): mixed
    {
        return $this->{$method}(...array_values($parameters));
    }

    /**
     * Magic method.
     * 
     * Dynamically handle calls to methods on the controller.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist', static::class, $method
        ));
    }
}