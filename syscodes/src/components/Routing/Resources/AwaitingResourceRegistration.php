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

use Syscodes\Components\Support\Arr;

/**
 * This class uses an awaiting resource registration instance.
 */
class AwaitingResourceRegistration
{
    /**
     * The resource controller.
     * 
     * @var string $controller
     */
    protected $controller;

    /**
     * The resource name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * The resource options.
     * 
     * @var array $options
     */
    protected $options = [];

    /**
     * The resource register.
     * 
     * @var \Syscodes\Components\Routing\Resources\ResourceRegister $register
     */
    protected $register;

    /**
     * The resource's registration status.
     * 
     * @var bool $registered
     */
    protected $registered = false;

    /**
     * Constructor. Create a new route resource registration instance.
     * 
     * @param  \Syscodes\Components\Routing\Resources\ResourceRegister  $register
     * @param  string  $name
     * @param  string  $controller
     * @param  array  $options
     * 
     * @return void
     */
    public function __construct(
        ResourceRegister $register, 
        $name, 
        $controller, 
        array $options = []
    ) {
        $this->name = $name;
        $this->options = $options;
        $this->register = $register;
        $this->controller = $controller;
    }

    /**
     * Set the methods the controller should apply to.
     * 
     * @param  array|string  $methods
     * 
     * @return static
     */
    public function only($methods): static
    {
        $this->options['only'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    /**
     * Set the methods the controller should exclude.
     * 
     * @param  array|string  $methods
     * 
     * @return static
     */
    public function except($methods): static
    {
        $this->options['except'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    /**
     * Set the route names for controller actions.
     * 
     * @param  array|string  $names
     * 
     * @return static
     */
    public function names($names): static
    {
        $this->options['names'] = $names;

        return $this;
    }

    /**
     * Set the route names for a controller action.
     * 
     * @param  string  $method
     * @param  string  $name
     * 
     * @return static
     */
    public function name($method, $name): static
    {
        $this->options['names'][$method] = $name;

        return $this;
    }

    /**
     * Override the route parameter names.
     * 
     * @param  array|string  $parameters
     * 
     * @return static
     */
    public function parameters($parameters): static
    {
        $this->options['parameters'] = $parameters;

        return $this;
    }

    /**
     * Override the route parameter's name.
     * 
     * @param  string  $previous
     * @param  string  $parameter
     * 
     * @return static
     */
    public function parameter($previous, $parameter): static
    {
        $this->options['parameters'][$previous] = $parameter;

        return $this;
    }
    
    /**
     * Add middleware to the resource routes.
     * 
     * @param  mixed  $middleware
     * 
     * @return \Syscodes\Components\Routing\Resources\AwaitingResourceRegistration
     */
    public function middleware($middleware)
    {
        $middleware = Arr::wrap($middleware);
        
        foreach ($middleware as $key => $value) {
            $middleware[$key] = (string) $value;
        }
        
        $this->options['middleware'] = $middleware;
        
        return $this;
    }

    /**
     * Register the resource route.
     * 
     * @return \Syscodes\Components\Routing\Collections\RouteCollection
     */
    public function register()
    {
        $this->registered = true;

        return $this->register->register(
            $this->name, $this->controller, $this->options
        );
    }

    /**
     * Magic method.
     * 
     * Handle the object's destruction.
     * 
     * @return void
     */
    public function __destruct()
    {
        if ( ! $this->registered) {
            $this->register();
        }
    }
}