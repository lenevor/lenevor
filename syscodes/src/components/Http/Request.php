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

namespace Syscodes\Http;

Use Locale;
use Closure;
use Exception;
use LogicException;
use Syscodes\Support\Str;
use Syscodes\Collections\Arr;
use Syscodes\Http\Contributors\Files;
use Syscodes\Http\Contributors\Inputs;
use Syscodes\Http\Contributors\Server;
use Syscodes\Http\Contributors\Headers;
use Syscodes\Http\Contributors\Parameters;

/**
 * Request represents an HTTP request.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Request
{
	/**
	 * Holds the global active request instance.
	 *
	 * @var bool $requestURI
	 */
	protected static $requestURI;

	/**
	 * The base URL.
	 * 
	 * @var string $baseUrl
	 */
	protected $baseUrl;

	/**
	 * Gets cookies ($_COOKIE).
	 * 
	 * @var string $cookies
	 */
	public $cookies;

	/**
	 * Gets the string with format JSON.
	 * 
	 * @var string|resource|null $content
	 */
	protected $content;

	/**
	 * The default Locale this request.
	 * 
	 * @var string $defaultLocale
	 */
	protected $defaultLocale = 'en';
	
	/**
	 * Gets files request ($_FILES).
	 * 
	 * @var string $files
	 */
	public $files;

	/**
	 * The detected uri and server variables.
	 * 
	 * @var string $http
	 */
	protected $http;

	/**
	 * The decoded JSON content for the request.
	 * 
	 * @var \Syscodes\Http\Contributors\Parameters|null $json
	 */
	protected $json;

	/**
	 * The current language of the application.
	 * 
	 * @var string $languages
	 */
	protected $languages;
	
	/** 
	 * The method name.
	 * 
	 * @var string $method
	 */
	protected $method;

	/**
	 * The path info of URL.
	 * 
	 * @var string $pathInfo
	 */
	protected $pathInfo;

	/**
	 * Request body parameters ($_POST).
	 * 
	 * @var \Syscodes\Http\Contributors\Parameters $request
	 */
	public $request;

	/**
	 * Get request URI.
	 * 
	 * @var string $requestToURI
	 */
	protected $requestToURI;

	/**
	 * Get the route resolver callback.
	 * 
	 * @var \Closure $routeResolver
	 */
	protected $routeResolver;

	/**
	 * The detected uri and server variables ($_FILES).
	 * 
	 * @var array $server
	 */
	public $server = [];

	/** 
	 * List of routes uri.
	 *
	 * @var string|array $uri 
	 */
	public $uri;

	/**
	 * Stores the valid locale codes.
	 * 
	 * @var array $validLocales
	 */
	protected $validLocales = [];

	/**
	 * Constructor: Create new the Request class.
	 * 
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * @param  string|resource|null $content  
	 * 
	 * @return void
	 */
	public function __construct(array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
	{
		static::$requestURI = $this;
		
		$this->initialize($request, $cookies, $files, $server, $content);

		$this->detectURI(config('app.uriProtocol'), config('app.baseUrl'));

		$this->detectLocale();
	}

	/**
	 * Sets the parameters for this request.
	 * 
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return void
	 */
	public function initialize(array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
	{
		$this->request      = new Parameters($request);
		$this->cookies      = new Inputs($cookies);
		$this->files        = new Files($files);
		$this->server       = new Server($server);
		$this->headers      = new Headers($this->server->all());

		$this->uri          = new URI;
		$this->http         = new Http;
		$this->method       = null;
		$this->baseUrl      = null;
		$this->content      = $content;
		$this->pathInfo     = null;
		$this->languages    = null;
		$this->validLocales = config('app.supportedLocales');
	}

	/**
	 * Create a new Syscodes HTTP request from server variables.
	 * 
	 * @return static
	 */
	public static function capture()
	{
		return static::createFromRequest(static::createFromRequestGlobals());
	}

	/**
	 * Creates an Syscodes request from of the Request class instance.
	 * 
	 * @param  \Syscodes\Http\Request  $request
	 * 
	 * @return static
	 */
	public static function createFromRequest($request)
	{
		$newRequest = (new static)->duplicate(
			$request->request->all(), $request->cookies->all(), 
			$request->files->all(), $request->server->all()
		);

		$newRequest->content = $request->content;

		return $newRequest;
	}

	/**
	 * Creates a new request with value from PHP's super global.
	 * 
	 * @return static
	 */
	public static function createFromRequestGlobals()
	{
		$request = static::createFromRequestFactory($_POST, $_COOKIE, $_FILES, $_SERVER);

		return $request;
	}

	/**
	 * Creates a new request from a factory.
	 * 
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return static
	 */
	protected static function createFromRequestFactory(array $request = [], array $cookies = [], array $files = [], array $server = [])
	{
		if (self::$requestURI) {
			$request = (self::$requestURI)($request, $cookies, $files, $server);

			if ( ! $request instanceof self) {
				throw new LogicException('The Request active must return an instance of Syscodes\Http\Request');
			}

			return $request;
		}

		return new static($request, $cookies, $files, $server);
	}

	/**
	 * Clones a request and overrides some of its parameters.
	 * 
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return static
	 */
	public function duplicate(array $request = [], array $cookies = [], array $files = [], array $server = [])
	{
		$duplicate = clone $this;

		if (null !== $request) {
			$duplicate->request = new Parameters($request);
		}

		if (null !== $cookies) {
			$duplicate->cookies = new Inputs($cookies);
		}

		if (null !== $files) {
			$duplicate->files = new Files($files);
		}

		if (null !== $server) {
			$duplicate->server  = new Server($server);
			$duplicate->headers = new Headers($duplicate->server->all());
		}

		$duplicate->uri          = new URI;
		$duplicate->http         = new Http;
		$duplicate->locale       = null;
		$duplicate->method       = null;
		$duplicate->baseUrl      = null;
		$duplicate->pathInfo     = null;
		$duplicate->validLocales = config('app.supportedLocales');

		return $duplicate;		
	}

	/**
	 * Returns the active request currently being used.
	 *
	 * @param  \Syscodes\Http\Request|bool|null  $request  Overwrite current request 
	 *                                                     before returning, false prevents 
	 *                                                     overwrite
	 *
	 * @return \Syscodes\Http\Request
	 */
	public static function active($request = false)
	{
		if ($request !== false) {
			static::$requestURI = $request;
		}

		return static::$requestURI;
	}

	/**
	 * Returns the desired segment, or $default if it does not exist.
	 *
	 * @param  int  $index  The segment number (1-based index)
	 * @param  mixed  $default  Default value to return
	 *
	 * @return  string
	 */
	public function segment($index, $default = null)
	{
		if ($request = static::active()) {
			return $request->uri->getSegment($index, $default);
		}

		return null;
	}

	/**
	 * Returns all segments in an array. For total of segments
	 * used the function PHP count().
	 *
	 * @return array
	 */
	public function segments()
	{
		if ($request = static::active()) {
			return $request->uri->getSegments();
		}

		return null;
	}

	/**
	 * Returns the total number of segment.
	 *
	 * @return int|null  
	 */
	public function totalSegments()
	{
		if ($request = static::active()) {
			return $request->uri->getTotalSegments();
		}

		return null;
	}

	/**
	 * Detects and returns the current URI based on a number of different server variables.
	 * 
	 * @param  string  $protocol
	 * @param  string  $baseUrl
	 * 
	 * @return string
	 */
	protected function detectURI(string $protocol, string $baseUrl)
	{
		$this->uri->setPath($this->http->detectPath($protocol));

		$baseUrl = ! empty($baseUrl) ? rtrim($baseUrl, '/ ').'/' : $baseUrl;

		if ( ! empty($baseUrl)) {
			$this->uri->setScheme(parse_url($baseUrl, PHP_URL_SCHEME));
			$this->uri->setHost(parse_url($baseUrl, PHP_URL_HOST));
			$this->uri->setPort(parse_url($baseUrl, PHP_URL_PORT));
		} else {
			if ( ! $this->http->isCli()) {
				exit('You have an empty or invalid base URL. The baseURL value must be set in config/app.php, or through the .env file.');
			}
		}
	}

	/**
	 * Handles setting up the locale, auto-detecting of language.
	 * 
	 * @return void
	 */
	public function detectLocale()
	{
		$this->languages = $this->defaultLocale = config('app.locale');

		$this->setLocale($this->validLocales);
	}

	/**
	 * Returns the default locale as set.
	 * 
	 * @return string
	 */
	public function getDefaultLocale()
	{
		return $this->defaultLocale;
	}

	/**
	 * Gets the current locale, with a fallback to the default.
	 * 
	 * @return string 
	 */
	public function getLocale()
	{
		return $this->languages ?: $this->defaultLocale;
	}

	/**
	 * Sets the locale string for this request.
	 * 
	 * @param  string  $locale
	 * 
	 * @return \Syscodes\Http\Request
	 */
	public function setLocale($locale)
	{
		if ( ! in_array($locale, $this->validLocales)) {
			$locale = $this->defaultLocale;
		}
		
		$this->languages = $locale;

		try {
		    if (class_exists('Locale', false)) {
				Locale::setDefault($locale);
			}
		} catch (Exception $exception) {}

		return $this;
	}

	/**
	 * Returns the full request string.
	 *
	 * @return string|null  The Request string
	 */
	public function get() 
	{
		if ($request = static::active()) {
			return $request->uri->getPath();
		}

		return null;
	}

	/**
	 * Get the JSON payload for the request.
	 * 
	 * @param  string|null  $key  
	 * @param  mixed  $default  
	 * 
	 * @return \Syscodes\Http\Contributors\Parameters|mixed
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
	 * Set the JSON payload for the request
	 * 
	 * @param  \Syscodes\Http\Contributors\Parameters  $json
	 * 
	 * @return $this
	 */
	public function setJson($json)
	{
		$this->json = $json;

		return $this;
	}

	/**
	 * Returns whether this is an AJAX request or not.
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest()
	{
		return ! empty($this->server->get('HTTP_X_REQUESTED_WITH')) && 
				strtolower($this->server->get('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Returns the input method used (GET, POST, DELETE, etc.).
	 *
	 * @return string
	 * 
	 * @throws \LogicException  
	 */
	public function getmethod()
	{
		if (null !== $this->method) {
			return $this->method;
		}
		
		$method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
		
		if (in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
			return $this->method = $method;
		}
		
		if ( ! preg_match('~^[A-Z]++$#~D', $method)) {
			throw new logicException(sprintf('Invalid method override "%s"', $method));
		}

		return $this->method = $method;
	}

	/**
	 * Sets the request method.
	 *
	 * @param  string  $method  
	 *
	 * @return string
	 */
	public function setMethod(string $method) 
	{
		$this->method = null;

		$this->server->set('REQUEST_METHOD', $method);
	}
	
	/**
	 * Determine if the current request URI matches a pattern.
	 * 
	 * @param  mixed  ...$patterns
	 * 
	 * @return bool
	 */
	public function is(...$patterns)
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
	public function routeIs(...$patterns)
	{
		return $this->route() && $this->route()->is(...$patterns);
	}

	/**
	 * Get the route handling the request.
	 * 
	 * @param  string|null  $param  
	 * @param  mixed  $default  
	 * 
	 * @return \Syscodes\Routing\Route|object|string|null
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
	public function decodedPath()
	{
		return rawurldecode($this->path());
	}

	/**
	 * Get the current path info for the request.
	 * 
	 * @return string
	 */
	public function path()
	{
		$path = trim($this->getPathInfo(), '/');

		return $path == '' ? '/' : $path;
	}

	/**
	 * Retunrs the request body content.
	 * 
	 * @return string
	 */
	public function getContent()
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
	public function getPathInfo()
	{
		if (null === $this->pathInfo) {
			$this->pathInfo = $this->http->parsePathInfo();
		}

		return $this->pathInfo;
	}

	/**
	 * Returns the root URL from which this request is executed.
	 * 
	 * @return string
	 */
	public function getBaseUrl()
	{
		if (null === $this->baseUrl) {
			$this->baseUrl = $this->http->parseBaseUrl();
		}

		return $this->baseUrl;
	}

	/**
	 * Returns the requested URI.
	 * 
	 * @return string
	 */
	public function getRequestUri()
	{
		if (null === $this->requestToUri) {
			$this->requestToUri = $this->http->parseRequestUri();
		}

		return $this->requestToUri;
	}
	
	/**
	 * Gets the request's scheme.
	 * 
	 * @return string
	 */
	public function getScheme()
	{
		return $this->secure() ? $this->uri->setScheme('https') : $this->uri->setScheme('http');
	}

	/**
	 * Returns the host name.
	 * 
	 * @return void
	 */
	public function getHost()
	{
		if ($forwardedHost = $this->server->get('HTTP_X_FORWARDED_HOST')) {
			$host = $forawardedHost[0];
		} elseif ( ! $host = $this->headers->get('HOST')) {
			if ( ! $host = $this->server->get('SERVER_NAME')) {
				$host = $this->server->get('REMOTE_ADDR', '');
			}
		}

		$host = $_SERVER['SERVER_NAME'];

		$host = strtolower(preg_replace('/:\d+$/', '', trim(($host))));
		
		return $host;
	}

	/**
	 * Returns the port on which the request is made.
	 * 
	 * @return int
	 */
	public function getPort()
	{
		if ( ! $this->server->get('HTTP_HOST')) {
			return $this->server->get('SERVER_PORT');
		}
		
		return 'https' === $this->getScheme() ? $this->uri->setPort(443) : $this->uri->setPort(80);
	}

	/**
	 * Returns the HTTP host being requested.
	 * 
	 * @return string
	 */
	public function getHttpHost()
	{
		$scheme = $this->getScheme();
		$port   = $this->getPort();

		if (('http' === $scheme && 80 === $port) || ('https' === $scheme && 443 === $port))	{
			return $this->getHost();
		}

		return $this->getHost().':'.$port;
	}

	/**
	 * Gets the scheme and HTTP host.
	 * 
	 * @return string
	 */
	public function getSchemeWithHttpHost()
	{
		return $this->getScheme().'://'.$this->getHttpHost();
	}

	/**
	 * Get the root URL for the application.
	 * 
	 * @return string
	 */
	public function root()
	{
		return rtrim($this->getSchemeWithHttpHost().$this->getBaseUrl(), '/');
	}

	/**
	 * Get the URL for the request.
	 * 
	 * @return string
	 */
	public function url()
	{
		return trim(preg_replace('/\?.*/', '', $this->get()), '/');
	}

	/**
	 * Returns the referer.
	 * 
	 * @param  string  $default
	 * 
	 * @return string
	 */
	public function referer(string $default = '')
	{
		return $this->server->get('HTTP_REFERER', $default);
	}
	
	/**
	 * Attempts to detect if the current connection is secure through 
	 * over HTTPS protocol.
	 * 
	 * @return bool
	 */
	public function secure()
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
	public function userAgent(string $default = null)
	{
		return $this->server->get('HTTP_USER_AGENT', $default);
	}

	/**
	 * Get the route resolver callback.
	 * 
	 * @return \Closure
	 */
	public function getRouteResolver()
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
	 * @return $this
	 */
	public function setRouteResolver(Closure $callback)
	{
		$this->routeResolver = $callback;

		return $this;
	}

	/**
	 * Get an element from the request.
	 * 
	 * @return string[]
	 */
	public function __get($key)
	{
		$all = $this->server->all();

		if (array_key_exists($key, $all)) {
			return $all[$key];
		} else {
			return $key;
		}
	}

	/**
	 * Returns the Request as an HTTP string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		try {
			$content = $this->getContent();
		} catch (LogicException $e) {
			if (PHP_VERSION_ID > 70400)	{
				throw $e;
			}

			return trigger_error($e, E_USER_ERROR);
		}

		$cookieHeader = '';
		$cookies      = [];

		foreach ($this->cookies as $key => $value) {
			$cookies[]= "{$key} = {$value}";
		}

		if ( ! empty($cookies)) {
			$cookieHeader = 'Cookie: '.implode('; ', $cookies)."\r\n";
		}
		
		return sprintf('%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get('SERVER_PROTOCOL'))."\r\n".
			$this->headers.
			$cookieHeader."\r\n".
			$content;
	}

	/**
	 * Clones the current request.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->request = clone $this->request;
		$this->cookies = clone $this->cookies;
		$this->files   = clone $this->files;
		$this->server  = clone $this->server;
		$this->headers = clone $this->headers;
	}
}