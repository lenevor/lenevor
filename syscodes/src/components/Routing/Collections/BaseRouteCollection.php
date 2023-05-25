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

namespace Syscodes\Components\Routing\Collections;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Routing\Matching\UriMatches;
use Syscodes\Components\Core\Http\Exceptions\NotFoundHttpException;

/**
 * Allows the route collection of base. 
 */
abstract class BaseRouteCollection implements Countable, IteratorAggregate
{
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
    protected function handleMatchedRoute(Request $request, $routes)
    {
        if ( ! is_null($route = $this->findRoute($routes, $request))) {
            return $route;
        }

        throw new NotFoundHttpException(sprintf(
            'The route "%s" could not be found', 
            $request->path()
        ));
    }

    /**
     * Find the first route matching a given request.
     *
     * @param  array  $routes
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Routing\Route|null
     */
    protected function findRoute($routes, $request)
    {
        $route = UriMatches::conditionLoopForRoutes($routes, $request);

        $parameters = [];

        $path = rtrim($request->path(), '/');
        
        // If the requested route one of the defined routes
        if (UriMatches::compareUri($route->uri, $path, $parameters, $route->wheres)) {
            return ! is_null($this->getCheckedRoutes($routes, $request)) 
                            ? $route->bind($request)
                            : $route;
        }
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
    protected function getCheckedRoutes(array $routes, $request, bool $method = true): Route|null
    {
        return collect($routes)->first(fn ($route) => $route->matches($request, $method));
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