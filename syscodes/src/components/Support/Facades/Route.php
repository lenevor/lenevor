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

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the Route class facade.
 * 
 * @method static \Syscodes\Components\Routing\Router any(string $route, string|Callable $action = null) 
 * @method static \Syscodes\Components\Routing\Router delete(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router get(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router head(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router match(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router options(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router patch(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router post(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Router put(string $route, string|Callable $action = null)
 * @method static \Syscodes\Components\Routing\Route addRoute(\Syscodes\Components\Routing\Route $route)
 * @method static array getAllRoutes()
 * @method static string getGroupPrefix()
 * @method static array getRoutesByMethod(array|string $method)
 * @method static void group(array $attributes, \Closure|string $callback)
 * @method static void map(array|string $method, string $route, mixed$action) 
 * @method static \Syscodes\Components\Routing\Route newRoute(array|string $method, string $uri, mixed $action)
 * @method static bool hasGroupStack()
 * @method static array resolve(\Syscodes\Components\Http\Request $request)
 * @method static string namespace(string $namespace = null)
 * 
 * @see \Syscodes\Components\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'router';
    }
}