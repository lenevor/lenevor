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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Matching;

use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;

/**
 * Checkes the request uri matches given route.
 */
class UriMatches
{
    /**
     * Check the regex if exist options of route for add conditionals.
     * 
     * @param  array  $routes
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    public static function patternLoopForRoutes(array $routes, Request $request)
    {
        foreach ($routes as $route) {
            if ( ! $route->fallback()) {
                continue;
            }

            $host = $route->getHost();

            if ($host !== null && $host != $request->getHost()) {
                continue;
            }
            
            $scheme = $route->getScheme();
            
            if ($scheme !== null && $scheme !== $request->getScheme()) {
                continue;
            }
            
            $port = $route->getPort();
            
            if ($port !== null && $port !== $request->getPort()) {
                continue;
            }

            $parameters = [];

            $path = rtrim($request->path(), '/');
            
            // If the requested route one of the defined routes
            if (UriMatches::compareUri($route->getRoute(), $path, $parameters, $route->getPatterns())) {
                return ! is_null(static::getCheckedRoutes($routes, $request)) 
                                ? $route->bind($request)
                                : $route;
            }
        }
    }
    
    /**
     * Check if given request uri matches given uri method.
     * 
     * @param  string  $route
     * @param  string  $uri
     * @param  string[]  $parameters
     * @param  string[]  $patterns
     * 
     * @return bool
     */
    public static function compareUri(
        string $route, 
        string $uri, 
        array &$parameters, 
        array $patterns
    ): bool {
        $regex = '~^'.static::regexUri($route, $patterns).'$~';
        
        return @preg_match($regex, $uri, $parameters);
    }
    
    /**
     * Convert route to regex.
     * 
     * @param  string  $route
     * @param  array  $patterns
     * 
     * @return string
     */
    private static function regexUri(string $route, array $patterns): string
    {
        return preg_replace_callback(
                    '~/\{([^}]+)\}~', 
                    fn (array $match) => static::regexParameter($match[1], $patterns), 
                    $route
                );
    }
    
    /**
     * Convert route parameter to regex.
     * 
     * @param  string  $name
     * @param  array  $patterns
     * 
     * @return string
     */
    private static function regexParameter(string $name, array $patterns): string
    {
        if ($name[-1] == '?') {
            $name = substr($name, 0, -1);
            $suffix = '?';
        } else {
            $suffix = '';
        }

        $pattern = $patterns[$name] ?? '[^/]+';
        
        return '/(?P<'.$name.'>'.$pattern.')'.$suffix;
    }

    /**
     * Determine if a route in the array matches the request.
     * 
     * @param  array  $routes
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  bool  $method
     * 
     * @return \Syscodes\Components\Routing\Route|null
     */
    private static function getCheckedRoutes(array $routes, Request $request, bool $method = true): Route|null
    {
        return collect($routes)->first(fn ($route) => $route->matches($request, $method));
    }
}