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

namespace Syscodes\Components\Routing;

use Stringable;
use Syscodes\Components\Routing\Exceptions\UrlGeneratorException;
use Syscodes\Components\Support\Arr;

use function Syscodes\Components\Support\enum_value;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Contracts\Routing\UrlRoutable;

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
     * @var array
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
        '%3F' => '?',
        '%26' => '&',
        '%23' => '#',
        '%25' => '%',
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
     * @var \Syscodes\Components\Routing\Generators\UrlGenerator
     */
    protected $url;

    /**
     * Constructor. Create a new RouteUrlGenerator class instance.
     * 
     * @param  \Syscodes\Components\Routing\Generators\UrlGenerator  $url
     * @param  \Syscodes\Components\Http\Request  $request 
     * @return void
     */
    public function __construct($url, $request)
    {
        $this->url = $url;
        $this->request = $request;        
    }

    /**
     * Generate a URL for the given route.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $parameters
     * @param  bool  $forced 
     * @return string
     * 
     * @throws \Syscodes\Components\Routing\Exceptions\UrlGeneratorException
     */
    public function to($route, $parameters = [], $forced = false): string
    {
        $parameters = $this->formatParameters($route, $parameters);
        
        $domain = $this->getRouteDomain($route, $parameters);

        $uri = $this->addQueryString($this->url->format(
            $root = $this->replaceRootParameters($route, $domain, $parameters),
            $this->replaceRouteParameters($route->getUri(), $parameters),
            $route
        ), $parameters);

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
     * Get the formatted domain for a given route.
     *
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $parameters
     * @return string|null
     */
    protected function getRouteDomain($route, &$parameters)
    {
        return $route->getDomain() ? $this->formatDomain($route, $parameters) : null;
    }

    /**
     * Format the domain and port for the route and request.
     *
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  array  $parameters
     * @return string
     */
    protected function formatDomain($route, &$parameters)
    {
        return $this->addPortToDomain(
            $this->getRouteScheme($route).$route->getDomain()
        );
    }

    /**
     * Format the array of route parameters.
     *
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  mixed  $parameters
     * @return array
     */
    protected function formatParameters(Route $route, $parameters)
    {
        $parameters = Arr::wrap($parameters);

        $namedParameters = [];
        $namedQueryParameters = [];
        $requiredRouteParametersWithoutDefaultsOrNamedParameters = [];

        $routeParameters = $route->parameterNames();
        $optionalParameters = $route->getOptionalParameters();

        foreach ($routeParameters as $name) {
            if (isset($parameters[$name])) {
                // Named parameters don't need any special handling...
                $namedParameters[$name] = $parameters[$name];
                unset($parameters[$name]);

                continue;
            } else {
                $bindingField = $route->bindingFieldFor($name);
                $defaultParameterKey = $bindingField ? "$name:$bindingField" : $name;

                if (! isset($this->defaultParameters[$defaultParameterKey]) && ! isset($optionalParameters[$name])) {
                    // No named parameter or default value for a required parameter, try to match to positional parameter below...
                    array_push($requiredRouteParametersWithoutDefaultsOrNamedParameters, $name);
                }
            }

            $namedParameters[$name] = '';
        }

        // Named parameters that don't have route parameters will be used for query string...
        foreach ($parameters as $key => $value) {
            if (is_string($key)) {
                $namedQueryParameters[$key] = $value;

                unset($parameters[$key]);
            }
        }

        // Match positional parameters to the route parameters that didn't have a value in order...
        if (count($parameters) == count($requiredRouteParametersWithoutDefaultsOrNamedParameters)) {
            foreach (array_reverse($requiredRouteParametersWithoutDefaultsOrNamedParameters) as $name) {
                if (count($parameters) === 0) {
                    break;
                }

                $namedParameters[$name] = array_pop($parameters);
            }
        }

        $offset = 0;
        $emptyParameters = array_filter($namedParameters, static fn ($val) => $val === '');

        if ($requiredRouteParametersWithoutDefaultsOrNamedParameters !== [] &&
            count($parameters) !== count($emptyParameters)) {
            // Find the index of the first required parameter...
            $offset = array_search($requiredRouteParametersWithoutDefaultsOrNamedParameters[0], array_keys($namedParameters));

            // If more empty parameters remain, adjust the offset...
            $remaining = count($emptyParameters) - $offset - count($parameters);

            if ($remaining < 0) {
                // Effectively subtract the remaining count since it's negative...
                $offset += $remaining;
            }

            // Correct offset if it goes below zero...
            if ($offset < 0) {
                $offset = 0;
            }
        } elseif ($requiredRouteParametersWithoutDefaultsOrNamedParameters === [] && count($parameters) !== 0) {
            // Handle the case where all passed parameters are for parameters that have default values...
            $remainingCount = count($parameters);

            // Loop over empty parameters backwards and stop when we run out of passed parameters...
            for ($i = count($namedParameters) - 1; $i >= 0; $i--) {
                if ($namedParameters[array_keys($namedParameters)[$i]] === '') {
                    $offset = $i;
                    $remainingCount--;

                    if ($remainingCount === 0) {
                        // If there are no more passed parameters, we stop here...
                        break;
                    }
                }
            }
        }

        // Starting from the offset, match any passed parameters from left to right...
        for ($i = $offset; $i < count($namedParameters); $i++) {
            $key = array_keys($namedParameters)[$i];

            if ($namedParameters[$key] !== '') {
                continue;
            } elseif (! empty($parameters)) {
                $namedParameters[$key] = array_shift($parameters);
            }
        }

        // Fill leftmost parameters with defaults if the loop above was offset...
        foreach ($namedParameters as $key => $value) {
            $bindingField = $route->bindingFieldFor($key);
            $defaultParameterKey = $bindingField ? "$key:$bindingField" : $key;

            if ($value === '' && isset($this->defaultParameters[$defaultParameterKey])) {
                $namedParameters[$key] = $this->defaultParameters[$defaultParameterKey];
            }
        }

        // Any remaining values in $parameters are unnamed query string parameters...
        $parameters = array_merge($namedParameters, $namedQueryParameters, $parameters);

        $parameters = Collection::wrap($parameters)->map(function ($value, $key) use ($route) {
            return $value instanceof UrlRoutable && $route->bindingFieldFor($key)
                    ? $value->{$route->bindingFieldFor($key)}
                    : $value;
        })->all();

        array_walk_recursive($parameters, function (&$item) {
            $item = enum_value($item);
        });

        return $this->url->formatParameters($parameters);
    }

    /**
     * Replace the parameters on the root path.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route
     * @param  string  $domain
     * @param  array  $parameters 
     * @return string
     */
    protected function replaceRootParameters($route, $domain, &$parameters): string
    {
        $scheme = $this->getRouteScheme($route);

        return $this->replaceRouteParameters(
            $this->url->formatRoot($scheme, $domain), $parameters
        );
    }
    
    /**
     * Replace all of the wildcard parameters for a route path.
     * 
     * @param  string  $path
     * @param  array  $parameters 
     * @return string
     */
    protected function replaceRouteParameters($path, array &$parameters)
    {
        $path = $this->replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            // Reset only the numeric keys...
            $parameters = array_merge($parameters);

            return ( ! isset($parameters[0]) && ! str_ends_with($match[0], '?}'))
                ? $match[0]
                : $this->encodeParameter(Arr::pull($parameters, 0));
        }, $path);

        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }
    
    /**
     * Replace all of the named parameters in the path.
     * 
     * @param  string  $path
     * @param  array  $parameters 
     * @return string
     */
    protected function replaceNamedParameters($path, array &$parameters)
    {
        return preg_replace_callback('/\{(.*?)(\?)?\}/', function ($m) use (&$parameters) {
            if (isset($parameters[$m[1]]) && $parameters[$m[1]] !== '') {
                return $this->encodeParameter(Arr::pull($parameters, $m[1]));
            } elseif (isset($this->defaultParameters[$m[1]])) {
                return $this->encodeParameter($this->defaultParameters[$m[1]]);
            } elseif (isset($parameters[$m[1]])) {
                Arr::pull($parameters, $m[1]);
            }

            return $m[0];
        }, $path);
    }

    /**
     * Encode a parameter value that is being substituted into a route URI.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function encodeParameter($value)
    {
        if ($value instanceof EncodedParameter) {
            return $value->value();
        }

        return is_string($value) || $value instanceof Stringable
            ? strtr((string) $value, ['%' => '%25', '?' => '%3F', '#' => '%23'])
            : $value;
    }

    /**
     * Add a query string to the URI.
     *
     * @param  string  $uri
     * @param  array  $parameters
     * @return mixed
     */
    protected function addQueryString($uri, array $parameters)
    {
        // If the URI has a fragment we will move it to the end of this URI since it will
        // need to come after any query string that may be added to the URL else it is
        // not going to be available.
        if ( ! is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT))) {
            $uri = preg_replace('/#.*/', '', $uri);
        }

        $uri .= $this->getRouteQueryString($parameters);

        return is_null($fragment) ? $uri : $uri."#{$fragment}";
    }

    /**
     * Add the port to the domain if necessary.
     * 
     * @param  string  $domain 
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
     * @return string
     */
    protected function getRouteRoot($route, $domain)
    {
        return $this->url->formatRoot($this->getRouteScheme($route), $domain);
    }

    /**
     * Get the scheme for the given route.
     * 
     * @param  \Syscodes\Components\Routing\Route  $route 
     * @return string
     */
    protected function getRouteScheme($route): string
    {
        if ($route->httpOnly()) {
            return $this->url->formatScheme(false);
        } elseif ($route->httpsOnly()) {
            return $this->url->formatScheme(true);
        }

        return $this->url->formatScheme();
    }
    
    /**
     * Get the query string for a given route.
     * 
     * @param  array  $parameters 
     * @return string
     */
    protected function getRouteQueryString(array $parameters): string
    {
        // First we will get all of the string parameters that are remaining after we
        // have replaced the route wildcards. We'll then build a query string from
        // these string parameters then use it as a starting point for the rest.
         if ($parameters === []) {
            return '';
        }

        $query = Arr::query(
            $keyed = $this->getStringParameters($parameters)
        );

        // Lastly, if there are still parameters remaining, we will fetch the numeric
        // parameters that are in the array and add them to the query string or we
        // will make the initial query string if it wasn't started with strings.
        if (count($keyed) < count($parameters)) {
            $query .= '&'.implode(
                '&', $this->getNumericParameters($parameters)
            );
        }

        $query = trim($query, '&');

        return $query === '' ? '' : "?{$query}";
    }
    
    /**
     * Get the string parameters from a given list.
     * 
     * @param  array  $parameters 
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
     * @return array
     */
    protected function getNumericParameters(array $parameters): array
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Set the default named parameters used by the URL generator.
     *
     * @param  array  $defaults
     * @return void
     */
    public function defaults(array $defaults): void
    {
        $this->defaultParameters = array_merge(
            $this->defaultParameters, $defaults
        );
    }
}