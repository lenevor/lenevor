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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Collections;

use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Support\Arr;

/**
 * Adds a collection to the arrays of routes.
 */
class RouteCollection extends BaseRouteCollection
{
    /**
     * Gets a table of routes by controller action.
     * 
     * @var \Syscodes\Components\Routing\Route[] $actionList
     */
    protected $actionList = [];

    /**
     * An array set to all of the routes.
     * 
     * @var \Syscodes\Components\Routing\Route[] $allRoutes
     */
    protected $allRoutes = [];

    /**
     * Gets a table of routes by their names.
     * 
     * @var \Syscodes\Components\Routing\Route[] $nameList
     */
    protected $nameList = [];

    /**
     * An array of the routes keyed by method.
     * 
     * @var array $routes
     */
    protected $routes = [];

    /**
     * Add a Route instance to the collection.
     * 
     * @param  \Syscodes\Components\Routing\Route  $routes
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    public function add(Route $route): Route
    {
        $this->addRouteCollections($route);

        $this->addRouteAllList($route);

        return $route;
    }

    /**
     * Add a given route to the arrays of routes.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * 
     * @return void
     */
    protected function addRouteCollections($route): void
    {
        $domainAndRoute = $route->domain().$route->getUri();

        foreach ($route->getMethod() as $method) {
            $this->routes[$method][$domainAndRoute] = $route;
        }

        $this->allRoutes[$method.$domainAndRoute] = $route;
    }

    /**
     * Add the route to the lookup tables if necessary.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * 
     * @return void
     */
    protected function addRouteAllList($route): void
    {
        if ($name = $route->getName()) {
            $this->nameList[$name] = $route;
        }

        $action = $route->getAction();

        if (isset($action['controller'])) {
            $this->AddToActionList($action, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
     * 
     * @param  array  $action
     * @param  \Syscodes\Components\Routing\route  $route
     * 
     * @return void
     */
    protected function AddToActionList($action, $route): void
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }

    /**
     * Refresh the name lookup table.
     * 
     * @return void
     */
    public function refreshNameLookups(): void
    {
        $this->nameList = [];

        foreach ($this->allRoutes as $route) {
            if ($route->getName()) {
                $this->nameList[$route->getName()] = $route;
            }
        }
    }

    /**
     * Refresh the action lookup table.
     * 
     * @return void
     */
    public function refreshActionLookups(): void
    {
        $this->actionList = [];

        foreach ($this->allRoutes as $route) {
            if (isset($route->getAction()['controller'])) {
                $this->AddToActionList($route->getAction(), $route);
            }
        }
    }
    
    /**
     * Get all of the routes keyed by their HTTP verb / method.
     * 
     * @return array
     */
    public function getRoutesByMethod(): array
    {
        return $this->routes;
    }    

    /**
     * Get all of the routes keyed by their name.
     * 
     * @return \Syscodes\Components\Routing\Route[]
     */
    public function getRoutesByName()
    {
        return $this->nameList;
    }

    /**
     * Find the first route matching a given request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    public function match(Request $request): Route
    {
        $routes = $this->get($request->getMethod());
        
        // First, we'll see if a matching path can be found for this current
        // request method. Great, if it works, it so can be called by the 
        // consumer. Otherwise we will check for routes with another verb.
        $route = $this->getMatchedRoutes($routes, $request);

        return $this->handleMatchedRoute($request, $route);
    }
    
    /**
     * Get routes from the collection by method.
     * 
     * @param  string|null  $method  (null by default)
     * 
     * @return \Syscodes\Components\Routing\Route[]
     */
    public function get($method = null)
    {
        return is_null($method) 
                      ? $this->getRoutes() 
                      : Arr::get($this->routes, $method, []);
    }

    /**
     * Determine if the route collection contains a given named route.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function hasNamedRoute(string $name): bool
    {
        return ! is_null($this->getByName($name));
    }

    /**
     * Get a route instance by its name.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Routing\Route|null
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
     * @return \Syscodes\Components\Routing\Route|null
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
    public function getRoutes(): array
    {
        return array_values($this->allRoutes);
    }
}