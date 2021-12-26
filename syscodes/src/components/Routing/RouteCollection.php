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
 */

namespace Syscodes\Components\Routing;

use Closure;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use BadMethodCallException;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Routing\Exceptions\RouteNotFoundException;

/**
 * Adds a collection to the arrays of routes.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class RouteCollection implements Countable, IteratorAggregate
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
	 * The Route instance.
	 * 
	 * @var \Syscodes\Components\Routing\Route|null
	 */
	protected $route;

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
        $domainAndRoute = $route->domain().$route->getRoute();

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
     * @param  \Sysodde\Routing\route  $route
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
     * 
     * @throws \Syscodes\Components\Routing\Exceptions\RouteNotFoundException
     */
    public function match(Request $request)
    {
        $routes = $this->get($request->getMethod()); 

        if ( ! is_null($route = $this->findRoute($request, $routes))) {
            return $route;
        }
        
        throw new RouteNotFoundException;
    }

    /**
     * Find the first route matching a given request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  array  $routes
     * 
     * @return \Syscodes\Components\Routing\Route;
     */
    protected function findRoute($request, array $routes)
    {        
        // Loop trough the possible routes
        foreach ($routes as $route) {
            // Variable assignment by route
            $this->route = $route;
            
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
            
            // If the requested route one of the defined routes
            if ($this->compareUri($route->getRoute(), $request->url())) {
                return $route->bind($request);
            }
        }
    }
    
    /**
     * Check if given request uri matches given uri method.
     * 
     * @param  string  $route
     * @param  string  $requestedUri
     * 
     * @return array
     */
    protected function compareUri(string $route, string $requestedUri): array
    {
        $pattern = '~^'.$this->regexUri($route).'$~';
        
        preg_match_all($pattern, $requestedUri, $match);

        return $match[0];
    }
    
    /**
     * Convert route to regex.
     * 
     * @param  string  $route
     * 
     * @return string
     */
    protected function regexUri(string $route): string
    {
        return preg_replace_callback('~/\{(.*?)(\?)?\}+~', function ($match) {
            return $this->regexParameter($match[1]);
        }, $route);
    }
    
    /**
     * Convert route parameter to regex.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function regexParameter(string $name): string
    {
        $pattern = $this->route->wheres[$name] ?? '[^/]+';
        
        return '/(?P<'.$name.'>'.$pattern.')';
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
