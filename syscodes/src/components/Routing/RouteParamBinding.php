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

namespace Syscodes\Routing;

use Syscodes\Collections\Arr;

/**
 * Allows the parameter matches for the path portion of the URI.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class RouteParamBinding
{
    /**
     * The route instance.
     * 
     * @var \Syscodes\Routing\Route $route
     */
    protected $route;

    /**
     * Constructor. Create a new Route parameter binder instance.
     * 
     * @param  \Syscodes\Routing\Route  $route
     * 
     * @return void
     */
    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     * Get the parameter for the route.
     * 
     * @param  \Syscodes\Http\Request  $request
     * 
     * @return array
     */
    public function parameters($request)
    {
        $parameters = $this->bindParameters($request);

        return $this->replaceDefaults($parameters);
    }

    /**
     * Get the parameter matches for the path portion of the URI.
     * 
     * @param  \Syscodes\Http\Request  $request
     * 
     * @return array
     */
    protected function bindParameters($request)
    {
        $path = '/'.ltrim($request->decodedPath(), '/');

        preg_match_all('#[^/]+$#sDu', $path, $matches);
        
        return $this->matchToKeys(array_slice($matches[0], 0));
    }
    
    /**
     * Combine a set of parameter matches with the route's keys.
     * 
     * @param  array  $matches
     * 
     * @return array
     */
    protected function matchToKeys(array $matches)
    {
        if (empty($parameterNames = $this->route->parameterNames())) {
            return [];
        }

        $parameters = array_intersect_key($matches, array_values($parameterNames));
        
        return array_filter($parameters, function ($value) {
            return is_string($value) && strlen($value) > 0;
        });
    }
    
    /**
     * Replace null parameters with their defaults.
     * 
     * @param  array  $parameters
     * 
     * @return array
     */
    protected function replaceDefaults(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $value ?? Arr::get($this->route->defaults, $key);
        }
        
        foreach ($this->route->defaults as $key => $value) {
            if ( ! isset($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }
        
        return $parameters;
    }
}