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
use RuntimeException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Loaders\Parameters;
use Syscodes\Components\Http\Session\SessionDecorator;
use Syscodes\Components\Http\Session\SessionInterface;
use Syscodes\Bundles\ApplicationBundle\Http\BaseRequest;
use Syscodes\Components\Http\Concerns\CanBePrecognitive;
use Syscodes\Components\Http\Concerns\InteractsWithInput;
use Syscodes\Components\Http\Concerns\InteractsWithFlashData;
use Syscodes\Components\Http\Concerns\InteractsWithContentTypes;
use Syscodes\Components\Http\Exceptions\SessionNotFoundException;

/**
 * Request represents an HTTP request.
 */
class Request extends BaseRequest
{
	use CanBePrecognitive,	    
	    InteractsWithInput,
	    InteractsWithFlashData,
	    InteractsWithContentTypes;

	/**
	 * The decoded JSON content for the request.
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Parameters|null $json
	 */
	protected $json;

	/**
	 * Get the route resolver callback.
	 * 
	 * @var \Closure $routeResolver
	 */
	protected $routeResolver;

	/**
	 * Create a new Syscodes HTTP request from server variables.
	 * 
	 * @return static
	 */
	public static function capture(): static
	{
		static::enabledHttpMethodParameterOverride();
		
		return static::createFromRequest(static::createFromRequestGlobals());
	}

	/**
	 * Creates an Syscodes request from of the Request class instance.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return static
	 */
	public static function createFromRequest($request): static
	{
		$newRequest = (new static)->duplicate(
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
	 * Returns the desired segment, or $default if it does not exist.
	 *
	 * @param  int  $index  The segment number (1-based index)
	 * @param  mixed  $default  Default value to return
	 *
	 * @return string
	 */
	public function segment($index, $default = null)
	{
		return $this->uri->getSegment($index, $default);
	}

	/**
	 * Returns all segments in an array. For total of segments
	 * used the function PHP count().
	 *
	 * @return array|null
	 */
	public function segments()
	{
		return $this->uri->getSegments();
	}

	/**
	 * Returns the total number of segment.
	 *
	 * @return int|null  
	 */
	public function totalSegments()
	{
		return $this->uri->getTotalSegments();
	}

	/**
	 * Returns the full request string.
	 * 
	 * @param  string  $key
	 * @param  mixed  $default
	 *
	 * @return mixed 
	 */
	public function get(string $key, $default = null) 
	{
		if ($this !== $result = $this->attributes->get($key, $this)) {
			return $result;
		}

		if ($this->query->has($key)) {
			return $this->query->all()[$key];
		}
		
		if ($this->request->has($key)) {
			return $this->request->all()[$key];
		}
		
		return $default;
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
	public function hasSession(): bool
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
	 * @return \Syscodes\Components\Http\Loaders\Parameters|mixed
	 */
	public function json($key = null, $default = null)
	{
		if ( ! isset($this->json)) {
			$this->json = new Parameters((array) json_decode($this->getContent(), true));
		}

		if (is_null($key)) {
			return $this->json;
		}

		return data_get($this->json->all(), $key, $default);
	}

	/**
	 * Set the JSON payload for the request.
	 * 
	 * @param  \Syscodes\Components\Http\Loaders\Parameters  $json
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
	 * Returns whether this is an AJAX request or not.
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest(): bool
	{
		return ! empty($this->server->get('HTTP_X_REQUESTED_WITH')) && 
				strtolower($this->server->get('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
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
		       strcasecmp($this->headers->get('Purpose') ?? '', 'prefetch') === 0;
	}

	/**
	 * Get the input source for the request.
	 * 
	 * @return \Syscodes\Components\Http\Loaders\Parameters
	 */
	public function getInputSource()
	{
		if ($this->isJson()) {
			return $this->json();
		}

		return in_array($this->getMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
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
		$path = $this->decodedPath();
		
		foreach ($patterns as $pattern) {
			if (Str::is($pattern, $path)) {
				return true;
			}
		}

		return false;
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
		return $this->route() && $this->route()->is(...$patterns);
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

		return $path == '' ? '/' : $path;
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
	 * Generates a normalized URI (URL) for the Request.
	 * 
	 * @return string
	 */
	public function getUri(): string
	{
		if (null !== $query = $this->getQueryString()) {
			$query = '?'.$query;
		}
		
		return $this->getSchemeWithHttpHost().$this->getBaseUrl().$this->getPathInfo().$query;
	}

	/**
	 * Get the root URL for the application.
	 * 
	 * @return string
	 */
	public function root(): string
	{
		return rtrim($this->getSchemeWithHttpHost().$this->getBaseUrl(), '/');
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
	 * Returns the user agent.
	 *
	 * @param  string|null  $default
	 *
	 * @return string
	 */
	public function userAgent(?string $default = null): string
	{
		return $this->server->get('HTTP_USER_AGENT', $default);
	}
	
	/**
	 * Get the client IP address.
	 * 
	 * @return string|null
	 */
	public function ip(): ?string
	{
		return $this->clientIp->getClientIp();
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
	 * Magic method.
	 * 
	 * Check if an input element is set on the request.
	 * 
	 * @param  string  $key
	 * 
	 * @return bool
	 */
	public function __isset($key)
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
	public function __get($key)
	{
		return Arr::get($this->all(), $key, fn () => $this->route($key));
	}
}