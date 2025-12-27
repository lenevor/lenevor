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

use Closure;

/**
 * Parses the middleware name and group.
 */
class RouteMiddlewareResolver
{
    /**
     * Resolve the middleware name to a class name(s) preserving passed parameters.
     * 
     * @param  \Closure|string  $name
     * @param  array  $map
     * @param  array  $middlewareGroups
     * 
     * @return \Closure|string|array
     */
    public static function resolve($name, $map, $middlewareGroups)
    {
        if ($name instanceof Closure) {
            return $name;
        }
        
        if (isset($map[$name]) && $map[$name] instanceof Closure) {
            return $map[$name];
        }
        
        if (isset($middlewareGroups[$name])) {
            return static::parseMiddlewareGroup($name, $map, $middlewareGroups);
        }
        
        [$name, $parameters] = array_pad(explode(':', $name, 2), 2, null);
        
        return ($map[$name] ?? $name).( ! is_null($parameters) ? ':'.$parameters : '');
    }
    
    /**
     * Parse the middleware group and format it for usage.
     * 
     * @param  string  $name
     * @param  array  $map
     * @param  array  $middlewareGroups
     * 
     * @return array
     */
    protected static function parseMiddlewareGroup($name, $map, $middlewareGroups): array
    {
        $results = [];
        
        foreach ($middlewareGroups[$name] as $middleware) {
            if (isset($middlewareGroups[$middleware])) {
                $results = array_merge($results, static::parseMiddlewareGroup(
                    $middleware, $map, $middlewareGroups
                ));
                
                continue;
            }
            
            [$middleware, $parameters] = array_pad(
                explode(':', $middleware, 2), 2, null
            );
            
            if (isset($map[$middleware])) {
                $middleware = $map[$middleware];
            }
            
            $results[] = $middleware.($parameters ? ':'.$parameters : '');
        }
        
        return $results;
    }
}