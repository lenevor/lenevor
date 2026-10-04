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

namespace Syscodes\Components\Routing\Resources;

use Syscodes\Components\Routing\Collections\RouteCollection;
use Syscodes\Components\Routing\Router;
use Syscodes\Components\Support\Str;

/**
 * Allows generate resources for routes register.
 */
class ResourceRegister
{
    /**
     * The parameters set for this resource instance.
     * 
     * @var array|string
     */
    protected $parameters;

    /**
     * The router instance.
     * 
     * @var \Syscodes\Components\Routing\Router
     */
    protected $router;

    /**
     * The defaults actions for a resource controller.
     * 
     * @var array
     */
    protected $resourceDefaults = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'
    ];

    /**
     * The default actions for a singleton resource controller.
     *
     * @var string[]
     */
    protected $singletonResourceDefaults = ['show', 'edit', 'update'];

    /**
     * The verbs used in the resource URIs.
     * 
     * @var array
     */
    protected static $verbs = [
        'create' => 'create',
        'edit'   => 'edit'
    ];

    /**
     * The global parameter mapping.
     *
     * @var array
     */
    protected static $parameterMap = [];

    /**
     * Singular global parameters.
     *
     * @var bool
     */
    protected static $singularParameters = true;

    /**
     * Constructor. Create a new resource register instance.
     * 
     * @param  \Syscodes\Components\Routing\Router  $router 
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Route a resource to a controller.
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Collections\RouteCollection
     */
    public function register($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && ! isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes 
        // to register these resource routes with a prefix so we will set that up out 
        // of the box so they don't have to mess with it. Otherwise, we will continue.
        if (Str::contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);

            return;
        }

        // We need to extract the base resource from the resource name. Nested resources 
        // are supported in the framework, but we need to know what name to use for a 
        // place-holder on the route wildcards, which should be the base resources.
        $segments = explode('.', $name);

        $base = $this->getResourceWilcard(last($segments));
        
        $collection = new RouteCollection;

        $methods = $this->getResourceMethods($this->resourceDefaults, $options);

        foreach ($methods as $method) {
            $route = $this->{'addResource'.ucfirst($method)}(
                $name, $base, $controller, $options
            );

            if (isset($options['bindingFields'])) {
                $this->setResourceBindingFields($route, $options['bindingFields']);
            }

            $collection->add($route);
        }

        return $collection;
    }

    /**
     * Route a singleton resource to a controller.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Collections\RouteCollection
     */
    public function singleton($name, $controller, array $options = [])
    {
        if (isset($options['parameters']) && ! isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these singleton routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
        if (Str::contains($name, '/')) {
            $this->prefixedSingleton($name, $controller, $options);

            return;
        }

        $defaults = $this->singletonResourceDefaults;

        if (isset($options['creatable'])) {
            $defaults = array_merge($defaults, ['create', 'store', 'destroy']);
        } elseif (isset($options['destroyable'])) {
            $defaults = array_merge($defaults, ['destroy']);
        }

        $collection = new RouteCollection;

        $resourceMethods = $this->getResourceMethods($defaults, $options);

        foreach ($resourceMethods as $method) {
            $route = $this->{'addSingleton'.ucfirst($method)}(
                $name, $controller, $options
            );

            if (isset($options['bindingFields'])) {
                $this->setResourceBindingFields($route, $options['bindingFields']);
            }

            $collection->add($route);
        }

        return $collection;
    }

    /**
     * Generates a set of prefixed resource routes.
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Router
     */
    public function prefixedResource($name, $controller, array $options)
    {
        [$name, $prefix] = $this->getResourcePrefix($name);

        $callback = function($router) use ($name, $controller, $options) {
            $router->resource($name, $controller, $options);
        };

        return $this->router->group(['prefix' => $prefix], $callback);
    }

    /**
     * Build a set of prefixed singleton routes.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Router
     */
    protected function prefixedSingleton($name, $controller, array $options)
    {
        [$name, $prefix] = $this->getResourcePrefix($name);

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
        $callback = function ($me) use ($name, $controller, $options) {
            $me->singleton($name, $controller, $options);
        };

        return $this->router->group(['prefix' => $prefix], $callback);
    }

    /**
     * Extract the resource and prefix from a resource name.
     * 
     * @param  string  $name 
     * @return array
     */
    protected function getResourcePrefix($name): array
    {
        $segments = explode('/', $name);

        $prefix = implode('/', array_slice($segments, 0, -1));

        return [last($segments), $prefix];
    }

    /**
     * Get the applicable resource methods.
     * 
     * @param  array  $defaults
     * @param  array  $options 
     * @return array
     */
    protected function getResourceMethods($defaults, array $options): array
    {
        $methods = $defaults;

        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }

        return array_values($methods);
    }

    /**
     * Add the index method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceIndex($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'index', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the create method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceCreate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/'.static::$verbs['create'];

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the store method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceStore($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'store', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the show method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceShow($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);
        
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the edit method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceEdit($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/'.static::$verbs['edit'];

        $action = $this->getResourceAction($name, $controller, 'edit', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the update method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceUpdate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the destroy method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceDestroy($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Add the create method for a singleton route.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addSingletonCreate($name, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/'.static::$verbs['create'];

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the store method for a singleton route.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addSingletonStore($name, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'store', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the show method for a singleton route.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addSingletonShow($name, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the edit method for a singleton route.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addSingletonEdit($name, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name).'/'.static::$verbs['edit'];

        $action = $this->getResourceAction($name, $controller, 'edit', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the update method for a singleton route.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addSingletonUpdate($name, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name);

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the destroy method for a singleton route.
     *
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addSingletonDestroy($name, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name);

        $action = $this->getResourceAction($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Get the name for a given resource with shallowness applied when applicable.
     *
     * @param  string  $name
     * @param  array  $options
     * @return string
     */
    protected function getShallowName($name, $options)
    {
        return isset($options['shallow']) && $options['shallow']
            ? last(explode('.', $name))
            : $name;
    }

    /**
     * Set the route's binding fields if the resource is scoped.
     *
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $bindingFields
     * @return void
     */
    protected function setResourceBindingFields($route, $bindingFields)
    {
        preg_match_all('/(?<={).*?(?=})/', $route->uri, $matches);

        $fields = array_fill_keys($matches[0], null);

        $route->setBindingFields(array_replace(
            $fields, array_intersect_key($bindingFields, $fields)
        ));
    }

    /**
     * Get the base resource URI for a given resource.
     * 
     * @param  string  $resource 
     * @return string
     */
    public function getResourceUri($resource): string
    {
        if ( ! Str::contains($resource, '.')) {
            return $resource;
        }

        $segments = explode('.', $resource);

        $uri = $this->getNestedResourceUri($segments);

        $name = $this->getResourceWilcard(last($segments));

        return str_replace('/{'.$name.'}', '', $uri);
    }

    /**
     * Get the URI for a nested resource segment array.
     * 
     * @param  array  $segments 
     * @return string
     */
    protected function getNestedResourceUri(array $segments): string
    {
        return implode('/', array_map(function ($segment) {
            return $segment.'/{'.$this->getResourceWilcard($segment).'}';
        }, $segments));
    }

    /**
     * Get the action array for a resource route.
     * 
     * @param  string  $resource
     * @param  string  $controller
     * @param  string  $method
     * @param  array  $options 
     * @return array
     */
    protected function getResourceAction($resource, $controller, $method, $options): array
    {
        $name = $this->getResourceRouteName($resource, $method, $options);

        $action = [
            'as' => $name,
            'uses' => $controller.'@'.$method
        ];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        if (isset($options['wheres'])) {
            $action['where'] = $options['wheres'];
        }

        if (isset($options['missing'])) {
            $action['missing'] = $options['missing'];
        }

        return $action;
    }

    /**
     * Get the name for a given resource.
     * 
     * @param  string  $resource
     * @param  string  $method
     * @param  array  $options 
     * @return string
     */
    protected function getResourceRouteName($resource, $method, $options): string
    {
        if (isset($options['names'])) {
            if (is_string($options['names'])) {
                $resource = $options['names'];
            } elseif (isset($options['names'][$method])) {
                return $options['names'][$method];
            }
        }

        $prefix = isset($options['as']) ? $options['as'].'.' : '';

        return trim(sprintf('%s%s.%s', $prefix, $resource, $method), '.');
    }

    /**
     * Format a resource parameter for usage.
     * 
     * @param  string  $value 
     * @return string
     */
    public function getResourceWilcard($value): string
    {
        if (isset(static::$parameters[$value])) {
            $value = static::$parameters[$value];
        } elseif (isset(static::$parameterMap[$value])) {
            $value = static::$parameterMap[$value];
        } elseif ($this->parameters === 'singular' || static::$singularParameters) {
            $value = Str::singular($value);
        } 

        return str_replace('-', '_', $value);
    }

    /**
     * Set or unset the unmapped global parameters to singular.
     *
     * @param  bool  $singular
     * @return void
     */
    public static function singularParameters($singular = true): void
    {
        static::$singularParameters = (bool) $singular;
    }

    /**
     * Get the global parameter map.
     *
     * @return array
     */
    public static function getParameters(): array
    {
        return static::$parameterMap;
    }

    /**
     * Set the global parameters mapping.
     * 
     * @param  array  $parameters 
     * @return void
     */
    public static function setParameters(array $parameters = []): void
    {
        static::$parameterMap = $parameters;
    }

    /**
     * Get or set the action verbs used in the resource URIs.
     * 
     * @param  array  $verbs 
     * @return array
     */
    public static function verbs(array $verbs = [])
    {
        if (empty($verbs)) {
            return static::$verbs;
        } 
        
        static::$verbs = array_merge(static::$verbs, $verbs);
    }
}