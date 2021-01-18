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
 * @since       0.5.1
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the Route class facade.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static \Syscodes\Routing\Router any(string $route, string|Callable $action = null) 
 * @method static \Syscodes\Routing\Router delete(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router get(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router head(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router match(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router options(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router patch(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router post(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Router put(string $route, string|Callable $action = null)
 * @method static \Syscodes\Routing\Route addRoute(\Syscodes\Routing\Route $route)
 * @method static array getAllRoutes()
 * @method static string getGroupPrefix()
 * @method static array getRoutesByMethod(array|string $method)
 * @method static void group(array $attributes, \Closure|string $callback)
 * @method static void map(array|string $method, string $route, mixed$action) 
 * @method static \Syscodes\Routing\Route newRoute(array|string $method, string $uri, mixed $action)
 * @method static bool hasGroupStack()
 * @method static array resolve(\Syscodes\Http\Request $request)
 * @method static string namespace(string $namespace = null)
 * 
 * @see \Syscodes\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}