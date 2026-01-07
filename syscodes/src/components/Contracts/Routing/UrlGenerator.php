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

namespace Syscodes\Components\Contracts\Routing;

use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Collections\RouteCollection;

/**
 * Returns the URL generated.
 */
interface UrlGenerator
{
    /**
     * Get the current URL for the request.
     * 
     * @return string
     */
    public function current(): string;
    
    /**
     * Get the URL for the previous request.
     * 
     * @param  mixed  $fallback  
     * 
     * @return string
     */
    public function previous($fallback = false): string;

    /**
     * Generate a absolute URL to the given path.
     * 
     * @param  string  $path
     * @param  mixed  $options
     * @param  bool|null  $secure
     * 
     * @return string
     */
    public function to($path, $options = [], $secure = null): string;

    /**
     * Generate a secure, absolute URL to the given path.
     * 
     * @param  string  $path
     * @param  array  $parameters
     * 
     * @return string
     */
    public function secure($path, $parameters = []): string;

    /**
     * Generate a URL to an application asset.
     * 
     * @param  string  $path
     * @param  bool|null  $secure  
     * 
     * @return string
     */
    public function asset($path, $secure = null): string;

    /**
     * Get the URL to a named route.
     * 
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $forced 
     * 
     * @return string
     * 
     * @throws \InvalidArgumentException
     */
    public function route($name, array $parameters = [], $forced = true): string;

    /**
     * Get the URL to a controller action.
     * 
     * @param  string  $action
     * @param  mixed  $parameters
     * @param  bool  $forced  
     * 
     * @return string
     * 
     * @throws \InvalidArgumentException
     */
    public function action($action, $parameters = [], $forced = true): string;

    /**
     * Sets the current Request instance.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return void
     */
    public function setRequest(Request $request): void;

    /**
     * Set the route collection.
     * 
     * @param  \Syscodes\Components\Routing\RouteCollection  $routes
     * 
     * @return static
     */
    public function setRoutes(RouteCollection $routes): static;

    /**
     * Set the session resolver for the generator.
     * 
     * @param  callable  $sessionResolver
     * 
     * @return static
     */
    public function setSessionResolver(callable $sessionResolver): static;
    
    /**
     * Set the encryption key resolver.
     * 
     * @param  callable  $keyResolver
     * 
     * @return static
     */
    public function setKeyResolver(callable $keyResolver): static;

    /**
     * Get the root controller namespace.
     * 
     * @return string
     */
    public function getRootControllerNamespace(): string;

    /**
     * Set the root controller namespace.
     * 
     * @param  string  $rootNamespace
     * 
     * @return static
     */
    public function setRootControllerNamespace($rootNamespace): static;
}