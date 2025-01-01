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

namespace Syscodes\Components\Routing\Collections;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Routing\Concerns\RouteRequestMatchesGiven;
use Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException;

/**
 * Allows the route collection of base. 
 */
abstract class BaseRouteCollection implements Countable, IteratorAggregate
{
    use RouteRequestMatchesGiven;

    /**
     * Handle the matched route.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Routing\Route|array|null  $routes
     * 
     * @return \Syscodes\Components\Routing\Route
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException
     */
    protected function handleMatchedRoute(Request $request, $route): Route
    {
        if ( ! is_null($route)) {
            return $route->bind($request);
        }

        throw new NotFoundHttpException(sprintf(
            'The route "%s" could not be found', 
            $request->path()
        ));
    }

    /**
     * Determine if a route in the array matches the request.
     * 
     * @param  array  $routes
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  bool  $method
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function getMatchedRoutes(array $routes, Request $request, bool $method = true)
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

            $path = rtrim($request->path());
            
            // If the requested route one of the defined routes
            if ($this->compareUri($route->getUri(), $path, $route->getPatterns())) {
                return $this->getMatchedToRegex($routes, $request, $method) 
                                                ? $route 
                                                : $route->fallback() ?? throw new InvalidArgumentException('Problems with matches of uri given');
            }
        }
    }

    /**
     * Check the regex if exist options of route for add conditionals.
     * 
     * @param  array  $routes
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    private function getMatchedToRegex(array $routes, Request $request, bool $method = true)
    {
        return collect($routes)->first(
            fn ($route) => $route->matches($request, $method)
        );
    }

    /*
    |-----------------------------------------------------------------
    | ArrayIterator Methods
    |-----------------------------------------------------------------
    */

    /**
     * Get an iterator for the items.
     * 
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getRoutes());
    }

    /*
    |-----------------------------------------------------------------
    | Countable Methods
    |-----------------------------------------------------------------
    */

    /**
     * Count the number of items in the collection.
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->getRoutes());
    }
}