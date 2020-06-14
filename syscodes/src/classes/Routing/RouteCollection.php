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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.0
 */

namespace Syscodes\Routing;

use Closure;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Support\Arr;
use Syscodes\Http\Request;
use BadMethodCallException;
use Syscodes\Core\Http\Exceptions\NotFoundHttpException;

/**
 * Adds a collection to the arrays of routes.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteCollection implements Countable, IteratorAggregate
{
    /**
     * An array of the routes keyed by method.
     * 
     * @var array $routes
     */
    protected $routes = [];

    /**
     * An array set to all of the routes.
     * 
     * @var \Syscodes\Routing\Route[] $allRoutes
     */
    protected $allRoutes = [];

    /**
     * Gets a table of routes by their names.
     * 
     * @var \Syscodes\Routing\Route[] $nameList
     */
    protected $nameList = [];

    /**
     * Gets a table of routes by controller action.
     * 
     * @var \Syscodes\Routing\Route[] $actionList
     */
    protected $actionList = [];

    /**
     * Add a Route instance to the collection.
     * 
     * @param  \Syscodes\Routing\Route  $routes
     * 
     * @return \Syscodes\Routing\Route
     */
    public function add(Route $route)
    {
        $this->addRouteCollections($route);

        //$this->addAllList($route);

        return $route;
    }

    /**
     * Add a given route to the arrays of routes.
     * 
     * @param  \Syscodes\Routing\Route  $route
     * 
     * @return void
     */
    protected function addRouteCollections($route)
    {
        $domainAndRoute = $route->domain().$route->getRoute();

        foreach ($route->getMethod() as $method)
        {
            $this->routes[$method][$domainAndRoute] = $route;
        }

        $this->allRoutes[$method.$domainAndRoute] = $route;
    }
    
    /**
     * Get all of the routes keyed by their HTTP verb / method.
     * 
     * @return array
     */
    public function getRoutesByMethod()
    {
        return $this->routes;
    }

    /**
     * Find the first route matching a given request.
     * 
     * @param  \Syscodes\Http\Request  $request
     * 
     * @return \Syscodes\Routing\Route
     * 
     * @throws \Syscodes\Core\Http\Exceptions\NotFoundHttpException
     */
    public function match(Request $request)
    {
        $routes = $this->get($request->method());

        if ( ! is_null($routes))
        {
            return $routes;
        }

        throw new NotFoundHttpException;
    }
    
    /**
     * Get routes from the collection by method.
     * 
     * @param  string|null  $method  (null by default)
     * 
     * @return \Syscodes\Routing\Route[]
     */
    public function get($method = null)
    {
        return is_null($method) ? $this->getRoutes() : Arr::get($this->routes, $method, []);
    }

    /**
     * Get all of the routes in the collection.
     * 
     * @return array
     */
    public function getRoutes()
    {
        return array_values($this->allRoutes);
    }

    /**
     * Get an iterator for the items.
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->getRoutes());
    }
}
