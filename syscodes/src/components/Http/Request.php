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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http;

use Closure;
use LogicException;
use RuntimeException;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Loaders\Parameters;
use Syscodes\Components\Http\Helpers\RequestUtils;
use Syscodes\Components\Http\Resources\HttpRequest;
use Syscodes\Components\Http\Resources\HttpResources;
use Syscodes\Components\Http\Session\SessionDecorator;
use Syscodes\Components\Http\Concerns\CanBePrecognitive;
use Syscodes\Components\Http\Concerns\InteractsWithInput;
use Syscodes\Components\Http\Concerns\InteractsWithFlashData;
use Syscodes\Components\Http\Concerns\InteractsWithContentTypes;
use Syscodes\Components\Http\Exceptions\SessionNotFoundException;

/**
 * Request represents an HTTP request.
 */
class Request
{
	use HttpRequest,
	    HttpResources,
	    CanBePrecognitive,	    
	    InteractsWithInput,
	    InteractsWithFlashData,
	    InteractsWithContentTypes;

	/**
	 * Get the acceptable of content types.
	 * 
	 * @var string[] $acceptableContenTypes
	 */
	protected $acceptableContentTypes;

	/**
	 * The decoded JSON content for the request.
	 * 
	 * @var \Syscodes\Components\Http\Loaders\Parameters|null $json
	 */
	protected $json;

	/**
	 * The path info of URL.
	 * 
	 * @var string $pathInfo
	 */
	protected $pathInfo;

	/**
	 * Get request URI.
	 * 
	 * @var string $requestToUri
	 */
	protected $requestToUri;

	/**
	 * Get the route resolver callback.
	 * 
	 * @var \Closure $routeResolver
	 */
	protected $routeResolver;

	/**
	 * The Session implementation.
	 * 
	 * @var \Syscodes\Components\Contracts\Session\Session $session
	 */
	protected $session;

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
	public function getSession()
	{
		$this->hasSession()
		            ? new SessionDecorator($this->session())
					: throw new SessionNotFoundException;
	}

	/**
	 * Whether the request contains a Session object.
	 * 
	 * @return bool
	 */
	public function hasSession(): bool
	{
		return ! is_null($this->session);
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
		
		return $this->session;
	}
	
	/**
	 * Set the session instance on the request.
	 * 
	 * @param  \Syscodes\Components\Contracts\Session\Session  $session
	 * 
	 * @return void
	 */
	public function setSession($session): void
	{
		$this->session = $session;
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

		return Arr::get($this->json->all(), $key, $default);
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
	 * Checks if the method request is of specified type.
	 * 
	 * @param  string  $method
	 * 
	 * @return bool
	 */
	public function isMethod(string $method): bool
	{
		return $this->getMethod() === strtoupper($method);
	}

	/**
     * Alias of the request method.
     * 
     * @return string
     */
    public function method(): string
    {
        return $this->getMethod();
    }

	/**
	 * Returns the input method used (GET, POST, DELETE, etc.).
	 *
	 * @return string
	 * 
	 * @throws \LogicException  
	 */
	public function getmethod(): string
	{
		if (null !== $this->method) {
			return $this->method;
		}
		
		$this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
		
		if ('POST' !== $this->method) {
			return $this->method;
		}
		
		$method = $this->headers->get('X-HTTP-METHOD-OVERRIDE');
		
		if ( ! $method && self::$httpMethodParameterOverride) {
			$method = $this->request->get('_method', $this->query->get('_method', 'POST'));
		}
		
		if ( ! is_string($method)) {
			return $this->method;
		}
		
		$method = strtoupper($method);
		
		if (in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
			return $this->method = $method;
		}
		
		if ( ! preg_match('/^[A-Z]++$/D', $method)) {
			throw new LogicException(sprintf('Invalid method override "%s".', $method));
		}
		
		return $this->method = $method;
	}

	/**
	 * Sets the request method.
	 *
	 * @param  string  $method  
	 *
	 * @return void
	 */
	public function setMethod(string $method): void
	{
		$this->method = null;

		$this->server->set('REQUEST_METHOD', $method);
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
	 * Generates the normalized query string for the Request.
	 * 
	 * @return string
	 */
	public function getQueryString(): string|null
	{
		$queryString = RequestUtils::normalizedQueryString($this->server->get('QUERY_STRING'));
		
		return '' === $queryString ? null : $queryString;
	}

	/**
	 * Retunrs the request body content.
	 * 
	 * @return string
	 */
	public function getContent(): string
	{
		if (null === $this->content || false === $this->content) {
			$this->content = file_get_contents('php://input');
		}

		return $this->content;
	}

	/**
	 * Returns the path being requested relative to the executed script. 
	 * 
	 * @return string
	 */
	public function getPathInfo(): string
	{
		if (null === $this->pathInfo) {
			$this->pathInfo = $this->parsePathInfo();
		}

		return $this->pathInfo;
	}

	/**
	 * Returns the root URL from which this request is executed.
	 * 
	 * @return string
	 */
	public function getBaseUrl(): string
	{
		if (null === $this->baseUrl) {
			$this->baseUrl = $this->parseBaseUrl();
		}

		return $this->baseUrl;
	}

	/**
	 * Returns the requested URI.
	 * 
	 * @return string
	 */
	public function getRequestUri(): string
	{
		if (null === $this->requestToUri) {
			$this->requestToUri = $this->parseRequestUri();
		}

		return $this->requestToUri;
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
		// Changed $this->path() for $this->getUri()
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
	 * Attempts to detect if the current connection is secure through 
	 * over HTTPS protocol.
	 * 
	 * @return bool
	 */
	public function secure(): bool
	{
		if ( ! empty($this->server->get('HTTPS')) && strtolower($this->server->get('HTTPS')) !== 'off') {
			return true;
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $this->server->get('HTTP_X_FORWARDED_PROTO') === 'https') {
			return true;
		} elseif ( ! empty($this->server->get('HTTP_FRONT_END_HTTPS')) && strtolower($this->server->get('HTTP_FRONT_END_HTTPS')) !== 'off') {
			return true;
		}

		return false;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param  string|null  $default
	 *
	 * @return string
	 */
	public function userAgent(string $default = null): string
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

	/**
	 * Magic method.
	 * 
	 * Returns the Request as an HTTP string.
	 * 
	 * @return string
	 */
	public function __toString(): string
	{
		$content = $this->getContent();

		$cookieHeader = '';
		$cookies      = [];

		foreach ($this->cookies as $key => $value) {
			$cookies[]= is_array($value) ? http_build_query([$key => $value], '', '; ', PHP_QUERY_RFC3986) : "$key=$value";
		}

		if ($cookies) {
			$cookieHeader = 'Cookie: '.implode('; ', $cookies)."\r\n";
		}
		
		return sprintf('%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get('SERVER_PROTOCOL'))."\r\n".
			$this->headers.
			$cookieHeader."\r\n".
			$content;
	}

	/**
	 * Magic method.
	 * 
	 * Clones the current request.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->query      = clone $this->query;
		$this->request    = clone $this->request;
		$this->attributes = clone $this->attributes;
		$this->cookies    = clone $this->cookies;
		$this->files      = clone $this->files;
		$this->server     = clone $this->server;
		$this->headers    = clone $this->headers;
	}
}