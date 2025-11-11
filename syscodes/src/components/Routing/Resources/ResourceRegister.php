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

namespace Syscodes\Components\Routing\Resources;

use Syscodes\Components\Support\Str;
use Syscodes\Components\Routing\Router;
use Syscodes\Components\Routing\Collections\RouteCollection;

/**
 * Allows generate resources for routes register.
 */
class ResourceRegister
{
    /**
     * The parameters set for this resource instance.
     * 
     * @param array|string $parameters
     */
    protected static $parameters;

    /**
     * The router instance.
     * 
     * @var \Syscodes\Components\Routing\Router $router
     */
    protected $router;

    /**
     * The defaults actions for a resource controller.
     * 
     * @var array $resourceDefaults
     */
    protected $resourceDefaults = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'erase'
    ];

    /**
     * The verbs used in the resource URIs.
     * 
     * @param array $verbs
     */
    protected static $verbs = [
        'create' => 'create',
        'edit'   => 'edit'
    ];

    /**
     * Constructor. Create a new resource register instance.
     * 
     * @param  \Syscodes\Components\Routing\Router  $router
     * 
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
     * 
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

        $methods = $this->getResourceMethods($this->resourceDefaults, $options);

        $collection = new RouteCollection;

        foreach ($methods as $method) {
            $collection->add($this->{'addResource'.ucfirst($method)}(
                $name, $base, $controller, $options
            ));
        }

        return $collection;
    }

    /**
     * Generates a set of prefixed resource routes.
     * 
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return string
     */
    public function prefixedResource($name, $controller, array $options)
    {
        [$name, $prefix] = $this->getResourcePrefix($name);

        $callback = function($router) use ($name, $controller, $options) {
            $router->resource($name, $controller, $options);
        };

        return $this->router->group(compact('prefix'), $callback);
    }

    /**
     * Extract the resource and prefix from a resource name.
     * 
     * @param  string  $name
     * 
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
     * @param  array  $default
     * @param  array  $options
     * 
     * @return array
     */
    protected function getResourceMethods($defaults, array $options): array
    {
        if (isset($options['only'])) {
            return array_intersect($defaults, (array) $options['only']);
        } elseif (isset($options['except'])) {
            return array_diff($defaults, (array) $options['except']);
        }

        return $defaults;
    }

    /**
     * Add the index method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceIndex($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

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
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceCreate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/'.static::$verbs['create'];

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
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceStore($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name);

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
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceShow($name, $base, $controller, $options)
    {
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
     * 
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
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceUpdate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'update', $options);

        return $this->router->match(['PUT', 'PATCH'], $uri, $action);
    }

    /**
     * Add the erase method for a resource route.
     * 
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return \Syscodes\Components\Routing\Route
     */
    protected function addResourceErase($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}';

        $action = $this->getResourceAction($name, $controller, 'erase', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Get the base resource URI for a given resource.
     * 
     * @param  string  $resource
     * 
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
     * 
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
     * 
     * @return array
     */
    protected function getResourceAction($resource, $controller, $method, $options): array
    {
        $name = $this->getResourceRouteName($resource, $method, $options);

        $action = [
            'as' => $name,
            'uses' => $controller.'@'.$method
        ];

        return $action;
    }

    /**
     * Get the name for a given resource.
     * 
     * @param  string  $resource
     * @param  string  $method
     * @param  array  $options
     * 
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
     * @param  string  $values
     * 
     * @return string
     */
    public function getResourceWilcard($value): string
    {
        if (isset(static::$parameters[$value])) {
            $value = static::$parameters[$value];
        }

        return str_replace('-', '_', $value);
    }

    /**
     * Set the global parameters.
     * 
     * @param  array  $parameters
     * 
     * @return void
     */
    public static function parameters(array $parameters = []): void
    {
        static::$parameters = $parameters;
    }

    /**
     * Get or set the action verbs used in the resource URIs.
     * 
     * @param  array  $verbs
     * 
     * @return array
     */
    public static function verbs(array $verbs = [])
    {
        if (empty($verbs)) {
            return static::$verbs;
        } else {
            static::$verbs = array_merge(static::$verbs, $verbs);
        }
    }
}