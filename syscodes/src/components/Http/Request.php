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

namespace Syscodes\Components\Http;

use Closure;
use ArrayAccess;
use RuntimeException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Session\SessionDecorator;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Http\Concerns\CanBePrecognitive;
use Syscodes\Components\Http\Concerns\InteractsWithInput;
use Syscodes\Components\Http\Concerns\InteractsWithFlashData;
use Syscodes\Components\Http\Concerns\InteractsWithContentTypes;
use Syscodes\Components\Http\Exceptions\SessionNotFoundException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Request represents an HTTP request.
 */
class Request extends SymfonyRequest implements Arrayable, ArrayAccess
{
	use CanBePrecognitive,	    
	    InteractsWithInput,
	    InteractsWithFlashData,
	    InteractsWithContentTypes;

	/**
	 * The decoded JSON content for the request.
	 * 
	 * @var \Symfony\Component\HttpFoundation\InputBag|null
	 */
	protected $json;

	/**
	 * Get the route resolver callback.
	 * 
	 * @var \Closure $routeResolver
	 */
	protected $routeResolver;
	
	/**
	 * The user resolver callback.
	 * 
	 * @var \Closure $userResolver
	 */
	protected $userResolver;

	/**
	 * Create a new Syscodes HTTP request from server variables.
	 * 
	 * @return static
	 */
	public static function capture(): static
	{
		static::enableHttpMethodParameterOverride();
		
		return static::createFromRequest(SymfonyRequest::createFromGlobals());
	}

	/**
	 * Creates an Syscodes request from of the Request class instance.
	 * 
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * 
	 * @return static
	 */
	public static function createFromRequest(SymfonyRequest $request): static
	{
		$newRequest = new static(
			$request->query->all(),
			$request->request->all(),
			$request->attributes->all(),
			$request->cookies->all(),
			$request->files->all(),
			$request->server->all()
		);
		
		$newRequest->headers->replace($request->headers->all());
		
		$newRequest->content = $request->content;
		
		if ($newRequest->isJson()) {
			$newRequest->request = $newRequest->json();
		}
		
		return $newRequest;
	}

	/**
	 * Get the specified URI segment, return default if it doesn't exist.
	 * Segment index is 1 based, not 0 based.
	 *
	 * @param  int  $index  The 1-based segment index
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public function segment(int $index, $default = null): mixed
	{
		return Arr::get($this->segments(), $index - 1, $default);
	}

	/**
	 * Returns the segments of the path as an array.
	 *
	 * @return array  The URI segments
	 */
	public function segments(): array
	{
		$segments = explode('/', $this->decodedPath());

        return array_values(array_filter($segments, function ($value) {
            return $value !== '';
        }));
	}

	/**
	 * Returns the total number of segment.
	 *
	 * @return int  
	 */
	public function totalSegment(): int
	{
		return count($this->segments());
	}

	/**
	 * Returns the full request string.
	 * 
	 * @param  string  $key
	 * @param  mixed  $default
	 * 
	 *
	 * @return mixed 
	 */
	#[\Override]
	public function get(string $key, mixed $default = null): mixed
	{
		return parent::get($key, $default);
	}
	
	/**
	 * Get the request method.
	 * 
	 * @return string
	 */
	public function method(): string
	{
		return $this->getMethod();
	}

	/**
	 * Gets the Session.
	 * 
	 * @return \Syscodes\Components\Http\Session\SessionInterface
	 * 
	 * @throws \Syscodes\Components\Http\Exceptions\SessionNotFoundException
	 */
	public function getSession(): SessionInterface
	{
		return $this->hasSession()
		            ? $this->session
					: throw new SessionNotFoundException;
	}

	/**
	 * Whether the request contains a Session object.
	 * 
	 * @return bool
	 */
	public function hasSession(bool $skipIfUninitialized = false): bool
	{
		return $this->session instanceof SessionDecorator;
	}

	/**
	 * Get the session associated with the request.
	 * 
	 * @return \Syscodes\Components\Contracts\Session\Session
	 * 
	 * @throws RuntimeException
	 */
	public function session()
	{
		if ( ! $this->hasSession()) {
			throw new RuntimeException('Session store not set on request');
		}
		
		return $this->session->store;
	}
	
	/**
	 * Set the session instance on the request.
	 * 
	 * @param  \Syscodes\Components\Contracts\Session\Session  $session
	 * 
	 * @return void
	 */
	public function setLenevorSession($session): void
	{
		$this->session = new SessionDecorator($session);
	}

	/**
	 * Get the JSON payload for the request.
	 * 
	 * @param  string|null  $key  
	 * @param  mixed  $default  
	 * 
	 * @return ($key is null ? \Symfony\Component\HttpFoundation\InputBag : mixed)
	 */
	public function json($key = null, $default = null)
	{
		if ( ! isset($this->json)) {
			$this->json = new InputBag((array) json_decode($this->getContent() ?: '[]', true));
		}

		if (is_null($key)) {
			return $this->json;
		}

		return data_get($this->json->all(), $key, $default);
	}

	/**
	 * Set the JSON payload for the request.
	 * 
	 * @param  \Symfony\Component\HttpFoundation\InputBag  $json
	 * 
	 * @return static
	 */
	public function setJson($json): static
	{
		$this->json = $json;

		return $this;
	}
	
	/**
	 * Gets a list of content types acceptable by the client browser in preferable order.
	 * 
	 * @return string[]
	 */
	public function getAcceptableContentTypes(): array
	{
		if (null !== $this->acceptableContentTypes) {
			return $this->acceptableContentTypes;
		}
		
		return $this->acceptableContentTypes = array_map('strval', [$this->headers->get('Accept')]);
	}

	/**
	 * Returns whether this is an AJAX request or not.
	 * Alias of isXmlHttpRequest().
	 *
	 * @return bool
	 */
	public function ajax(): bool
	{
		return $this->isXmlHttpRequest();
	}
	
	/**
	 * Determine if the request is the result of a PJAX call.
	 * 
	 * @return bool
	 */
	public function pjax(): bool
	{
		return $this->headers->get('X-PJAX') == true;
	}
	
	/**
	 * Determine if the request is the result of a prefetch call.
	 * 
	 * @return bool
	 */
	public function prefetch(): bool
	{
		return strcasecmp($this->server->get('HTTP_X_MOZ') ?? '', 'prefetch') === 0 ||
		       strcasecmp($this->headers->get('Purpose') ?? '', 'prefetch') === 0 ||
               strcasecmp($this->headers->get('Sec-Purpose') ?? '', 'prefetch') === 0;
	}
	
	/**
	 * Determine if the request is over HTTPS.
	 * 
	 * @return bool
	 */
	public function secure(): bool
	{
		return $this->isSecure();
	}

	/**
     * Replace the input for the current request.
     * 
     * @param  array  $key
     * 
     * @return static
     */
    public function replace(array $key): static
    {
        $this->getInputSource()->replace($key);

		return $this;
    }

	/**
	 * Get the input source for the request.
	 * 
	 * @return \Symfony\Component\HttpFoundation\InputBag
	 */
	public function getInputSource()
	{
		if ($this->isJson()) {
			return $this->json();
		}

		return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
	}
	
	/**
	 * Determine if the current request URI matches a pattern.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function is(...$patterns): bool
	{
	    return (new Collection($patterns))
		       ->contains(fn ($pattern) => Str::is($pattern, $this->decodedPath()));
	}

	/**
	 * Determine if the route name matches a given pattern.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function routeIs(...$patterns): bool
	{
		return $this->route() && $this->route()->named(...$patterns);
	}

	/**
	 * Get the route handling the request.
	 * 
	 * @param  string|null  $param  
	 * @param  mixed  $default  
	 * 
	 * @return \Syscodes\Components\Routing\Route|object|string|null
	 */
	public function route($param = null, $default = null)
	{
		$route = call_user_func($this->getRouteResolver());

		if (is_null($route) || is_null($param)) {
			return $route;
		}

		return $route->parameter($param, $default);
	}

	/**
     * Get the host name.
     *
     * @return string
     */
    public function host()
    {
        return $this->getHost();
    }

    /**
     * Get the HTTP host being requested.
     *
     * @return string
     */
    public function httpHost()
    {
        return $this->getHttpHost();
    }

    /**
     * Get the scheme and HTTP host.
     *
     * @return string
     */
    public function schemeAndHttpHost()
    {
        return $this->getSchemeAndHttpHost();
    }
	
	/**
	 * Get the user making the request.
	 * 
	 * @param  string|null  $guard
	 * 
	 * @return mixed
	 */
	public function user($guard = null)
	{
		return call_user_func($this->getUserResolver(), $guard);
	}

	/**
	 * Get the current decoded path info for the request.
	 * 
	 * @return string
	 */
	public function decodedPath(): string
	{
		return rawurldecode($this->path());
	}

	/**
	 * Get the current path info for the request.
	 * 
	 * @return string
	 */
	public function path(): string
	{
		$path = trim($this->getPathInfo(), '/');

		return $path === '' ? '/' : $path;
	}

	/**
	 * Get the full URL for the request.
	 * 
	 * @return string
	 */
	public function fullUrl(): string
	{
		$query = $this->getQueryString();
		
		$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';
		
		return $query ? $this->url().$question.$query : $this->url();
	}

	/**
	 * Get the root URL for the application.
	 * 
	 * @return string
	 */
	public function root(): string
	{
		return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
	}

	/**
	 * Get the URL for the request.
	 * 
	 * @return string
	 */
	public function url(): string
	{
		return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
	}

	/**
	 * Returns the referer.
	 * 
	 * @param  string  $default
	 * 
	 * @return string
	 */
	public function referer(string $default = ''): string
	{
		return $this->server->get('HTTP_REFERER', $default);
	}
	
	/**
	 * Get the client IP address.
	 * 
	 * @return string|null
	 */
	public function ip(): ?string
	{
		return $this->getClientIp();
	}
	
	/**
	 * Get the client IP addresses.
	 * 
	 * @return array
	 */
	public function ips(): array
	{
		return $this->getClientIps();
	}
	
	/**
	 * Get the client user agent.
	 * 
	 * @return string|null
	 */
	public function userAgent(): string|null
	{
		return $this->headers->get('User-Agent');
	}
	
	/**
	 * Get the user resolver callback.
	 * 
	 * @return \Closure
	 */
	public function getUserResolver(): Closure
	{
		return $this->userResolver ?: function () {
			//
		};
	}
	
	/**
	 * Set the user resolver callback.
	 * 
	 * @param  \Closure  $callback
	 * 
	 * @return static
	 */
	public function setUserResolver(Closure $callback): static
	{
		$this->userResolver = $callback;
		
		return $this;
	}

	/**
	 * Get the route resolver callback.
	 * 
	 * @return \Closure
	 */
	public function getRouteResolver(): Closure
	{
		return $this->routeResolver ?: function () {
			//
		};
	}

	/**
	 * Set the route resolver callback.
	 * 
	 * @param  \Closure  $callback
	 * 
	 * @return static
	 */
	public function setRouteResolver(Closure $callback): static
	{
		$this->routeResolver = $callback;

		return $this;
	}
	
	/**
	 * Get all of the input and files for the request.
	 * 
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->all();
	}

	/**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
	 * 
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $route = $this->route();

        return Arr::has(
            $this->all() + ($route ? $route->parameters() : []),
            $offset
        );
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
	 * 
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
	 * 
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->getInputSource()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param  string  $offset
	 * 
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->getInputSource()->remove($offset);
    }
	
	/**
	 * Magic method.
	 * 
	 * Check if an input element is set on the request.
	 * 
	 * @param  string  $key
	 * 
	 * @return bool
	 */
	public function __isset(string $key): bool
	{
		return ! is_null($this->__get($key));
	}

	/**
	 * Magic method.
	 * 
	 * Get an element from the request.
	 * 
	 * @return string[]
	 */
	public function __get(string $key): mixed
	{
		return Arr::get($this->all(), $key, fn () => $this->route($key));
	}
}