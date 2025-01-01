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

namespace Syscodes\Components\Routing\Generators;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Routing\Exceptions\UrlGeneratorException;

/**
 * Allows generate a URL for the given route.
 */
class RouteUrlGenerator
{
    /**
     * The named parameter defaults.
     * 
     * @var array
     */
    public $defaultParameters = [];
    
    /**
     * Characters that should not be URL encoded.
     * 
     * @var array $dontEncode
     */
    protected $dontEncode = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
    ];

    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request
     */
    protected $request;

    /**
     * The URL generator instance.
     * 
     * @var \Syscodes\Components\Routing\UrlGenerator
     */
    protected $url;

    /**
     * Constructor. Create a new RouteUrlGenerator class instance.
     * 
     * @param  \Syscodes\Components\Routing\Generators\UrlGenerator  $url
     * @param  \Syscodes\Components\Http\Request
     * 
     * @return void
     */
    public function __construct($url, $request)
    {
        $this->url     = $url;
        $this->request = $request;        
    }

    /**
     * Generate a URL for the given route.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $parameters
     * @param  bool  $forced
     * 
     * @return string
     * 
     * @throws Syscodes\Components\Routing\Exceptions\UrlGeneratorException
     */
    public function to($route, $parameters = [], $forced = false): string
    {
        $domain = $this->getRouteDomain($route, $parameters);
        
        $root = $this->replaceRoot($route, $domain, $parameters);

        $uri = $this->url->format(
                    $root,
                    $this->replaceRouteParameters($route->getUri(), $parameters)
               );

        if (preg_match_all('/{(.*?)}/', $uri, $missingParameters)) {
            throw UrlGeneratorException::missingParameters($route, $missingParameters[1]);
        }

        // Once we have ensured that there are no missing parameters in the URI we will encode
        // the URI and prepare it for returning to the developer. If the URI is supposed to
        // be absolute, we will return it as-is. Otherwise we will remove the URL's root.
        $uri = strtr(rawurlencode($uri), $this->dontEncode).$this->getRouteQueryString($parameters);

        if ( ! $forced) {
            $uri = preg_replace('#^(//|[^/?])+#', '', $uri);

            if ($base = $this->request->getBaseUrl()) {
                $uri = preg_replace('#^'.$base.'#i', '', $uri);
            }

            return '/'.ltrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Replace the parameters on the root path.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  string  $domain
     * @param  array  $parameters
     * 
     * @return string
     */
    protected function replaceRoot($route, $domain, &$parameters): string
    {
        return $this->replaceRouteParameters($this->getRouteRoot($route, $domain), $parameters);
    }
    
    /**
     * Replace all of the wildcard parameters for a route path.
     * 
     * @param  string  $path
     * @param  array  $parameters
     * 
     * @return string
     */
    protected function replaceRouteParameters($path, array &$parameters): string
    {
        if (count($parameters) > 0) {
            $path = preg_replace_sub(
                '/\{.*?\}/', $parameters, $this->replaceNamedParameters($path, $parameters)
            );
        }
        
        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }
    
    /**
     * Replace all of the named parameters in the path.
     * 
     * @param  string  $path
     * @param  array  $parameters
     * 
     * @return string
     */
    protected function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback(
                    '/\{(.*?)\??\}/', 
                    fn ($match) => isset($parameters[$match[1]]) ? Arr::pull($parameters, $match[1]) : $match[0], 
                    $path
                );
    }

    /**
     * Get the formatted domain for a given route.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $parameters
     * 
     * @return string|null
     */
    protected function getRouteDomain($route, &$parameters)
    {
        return $route->domain() ? $this->formatDomain($route, $parameters) : null;
    }

    /**
     * Format the domain and port for the route and request.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array $parameters
     * 
     * @return string
     */
    protected function formatDomain($route, &$parameters): string
    {
        return $this->addPortToDomain($this->getDomainAndScheme($route));
    }

    /**
     * Add the port to the domain if necessary.
     * 
     * @param  string  $domain
     * 
     * @return string
     */
    protected function addPortToDomain($domain): string
    {
        if (in_array($this->request->getPort(), [80, 443])) {
            return $domain;
        }

        return $domain.':'.$this->request->getPort();
    }

    /**
     * Get the domain and scheme for the route.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * 
     * @return string
     */
    protected function getDomainAndScheme($route): string
    {
        return $this->getRouteScheme($route).$route->domain();
    }

    /**
     * Get the root of the route URL.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  string  $domain
     * 
     * @return string
     */
    protected function getRouteRoot($route, $domain): string
    {
        return $this->url->getRootUrl($this->getRouteScheme($route), $domain);
    }

    /**
     * Get the scheme for the given route.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * 
     * @return string
     */
    protected function getRouteScheme($route): string
    {
        if ($route->httpOnly) {
            return $this->url->getScheme(false);
        } elseif ($route->httpsOnly) {
            return $this->url->getScheme(true);
        }

        return $this->url->getScheme(null);
    }
    
    /**
     * Get the query string for a given route.
     * 
     * @param  array  $parameters
     * 
     * @return string
     */
    protected function getRouteQueryString(array $parameters): string
    {
        // First we will get all of the string parameters that are remaining after we
        // have replaced the route wildcards. We'll then build a query string from
        // these string parameters then use it as a starting point for the rest.
        if (count($parameters) == 0) {
            return '';
        }
        
        $query = http_build_query(
            $keyed = $this->getStringParameters($parameters)
        );
        
        // Lastly, if there are still parameters remaining, we will fetch the numeric
        // parameters that are in the array and add them to the query string or we
        // will make the initial query string if it wasn't started with strings.
        if (count($keyed) < count($parameters)) {
            $query .= '&' .implode('&', $this->getNumericParameters($parameters));
        }
        
        return '?' .trim($query, '&');
    }
    
    /**
     * Get the string parameters from a given list.
     * 
     * @param  array  $parameters
     * 
     * @return array
     */
    protected function getStringParameters(array $parameters): array 
    {
        return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }
    
    /**
     * Get the numeric parameters from a given list.
     * 
     * @param  array  $parameters
     * 
     * @return array
     */
    protected function getNumericParameters(array $parameters): array
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }
}