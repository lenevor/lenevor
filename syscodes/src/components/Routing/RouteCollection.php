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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.2
 */

namespace Syscodes\Routing;

use Closure;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Http\Request;
use BadMethodCallException;
use Syscodes\Collections\Arr;

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

        $this->addRouteAllList($route);

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
     * Add the route to the lookup tables if necessary.
     * 
     * @param  \Syscodes\Routing\Route  $route
     * 
     * @return void
     */
    protected function addRouteAllList($route)
    {
        if ($name = $route->getName())
        {
            $this->nameList[$name] = $route;
        }

        $action = $route->getAction();

        if (isset($action['controller']))
        {
            $this->AddToActionList($action, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
     * 
     * @param  array  $action
     * @param  \Sysodde\Routing\route  $route
     * 
     * @return void
     */
    protected function AddToActionList($action, $route)
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }

    /**
     * Refresh the name lookup table.
     * 
     * @return void
     */
    public function refreshNameLookups()
    {
        $this->nameList = [];

        foreach ($this->allRoutes as $route)
        {
            if ($route->getName())
            {
                $this->nameList[$route->getName()] = $route;
            }
        }
    }

    /**
     * Refresh the action lookup table.
     * 
     * @return void
     */
    public function refreshActionLookups()
    {
        $this->actionList = [];

        foreach ($this->allRoutes as $route)
        {
            if (isset($route->getAction()['controller']))
            {
                $this->AddToActionList($route->getAction(), $route);
            }
        }
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
     * Get all of the routes keyed by their name.
     * 
     * @return \Syscodes\Routing\Route[]
     */
    public function getRoutesByName()
    {
        return $this->nameList;
    }

    /**
     * Find the first route matching a given request.
     * 
     * @param  \Syscodes\Http\Request  $request
     * 
     * @throws \Syscodes\Routing\Exceptions\RouteNotFoundException;
     */
    public function match(Request $request)
    {
        $route = $this->get($request->getMethod());

        return $route;
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
     * Determine if the route collection contains a given named route.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function hasNamedRoute(string $name)
    {
        return ! is_null($this->getByName($name));
    }

    /**
     * Get a route instance by its name.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Routing\Route|null
     */
    public function getByName(string $name)
    {
        return $this->nameList[$name] ?? null;
    }

    /**
     * Get a route instance by its controller action.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Routing\Route|null
     */
    public function getByAction(string $name)
    {
        return $this->actionList[$name] ?? null;
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
    public function getIterator()
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
    public function count()
    {
        return count($this->getRoutes());
    }
}
