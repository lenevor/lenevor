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
use Syscodes\Components\Contracts\Support\Arrayable;
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
    public function handleMatchedRoute(Request $request, $routes)
    {
        if ( ! is_null($routes)) {
             // Loop trough the possible routes
            foreach ($routes as $route) {   
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
                if (UriMatches::compareUri($route->uri, $path, $parameters, $route->wheres)) {
                    return $route->bind($request);
                }
            }
        }

        throw new NotFoundHttpException(sprintf(
            'The route "%s" could not be found', $request->path()
        ));
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