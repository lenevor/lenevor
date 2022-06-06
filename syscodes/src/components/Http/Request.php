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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http;

Use Locale;
use Closure;
use Exception;
use LogicException;
use RuntimeException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Collections\Arr;
use Syscodes\Components\Http\Contributors\Files;
use Syscodes\Components\Http\Contributors\Inputs;
use Syscodes\Components\Http\Contributors\Server;
use Syscodes\Components\Http\Contributors\Headers;
use Syscodes\Components\Http\Contributors\Parameters;
use Syscodes\Components\Http\Session\SessionDecorator;
use Syscodes\Components\Http\Session\SessionInterface;
use Syscodes\Components\Http\Concerns\InteractsWithInput;
use Syscodes\Components\Http\Concerns\InteractsWithContentTypes;
use Syscodes\Components\Http\Exceptions\SessionNotFoundException;

/**
 * Request represents an HTTP request.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Request
{
	use InteractsWithInput,
	    InteractsWithContentTypes;

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
	 * @var string|object $cookies
	 */
	public $cookies;

	/**
	 * Gets the string with format JSON.
	 * 
	 * @var string|resource|object|null $content
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
	 * @var string|object $files
	 */
	public $files;

	/**
	 * The detected uri and server variables.
	 * 
	 * @var string|object $http
	 */
	protected $http;

	/**
	 * The decoded JSON content for the request.
	 * 
	 * @var \Syscodes\Components\Http\Contributors\Parameters|null $json
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
	 * Query string parameters ($_GET).
	 * 
	 * @var \Syscodes\Components\Http\Contributors\Parameters $queryString
	 */
	public $queryString;

	/**
	 * Request body parameters ($_POST).
	 * 
	 * @var \Syscodes\Components\Http\Contributors\Parameters $request
	 */
	public $request;

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
	 * The detected uri and server variables ($_FILES).
	 * 
	 * @var array|object $server
	 */
	public $server = [];

	/** 
	 * List of routes uri.
	 *
	 * @var string|array|object $uri 
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
	 * @param  array  $queryString
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * @param  string|resource|null $content  
	 * 
	 * @return void
	 */
	public function __construct(array $queryString = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
	{
		static::$requestURI = $this;
		
		$this->initialize($queryString, $request, $cookies, $files, $server, $content);
		
		$this->detectURI(config('app.uriProtocol'), config('app.baseUrl'));

		$this->detectLocale();
	}

	/**
	 * Sets the parameters for this request.
	 * 
	 * @param  array  $queryString
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return void
	 */
	public function initialize(
		array $queryString = [], 
		array $request = [], 
		array $cookies = [], 
		array $files = [], 
		array $server = [], 
		$content = null
	): void {
		$this->queryString  = new Inputs($queryString);
		$this->request      = new Inputs($request);
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
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return static
	 */
	public static function createFromRequest($request)
	{
		$newRequest = (new static)->duplicate(
			$request->queryString->all(), $request->request->all(), $request->cookies->all(), 
			$request->files->all(), $request->server->all()
		);

		$newRequest->content = $request->content;
		$newRequest->request = $newRequest->getInputSource();

		return $newRequest;
	}

	/**
	 * Creates a new request with value from PHP's super global.
	 * 
	 * @return static
	 */
	public static function createFromRequestGlobals()
	{
		$request = static::createFromRequestFactory($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);

		parse_str($request->getContent(), $data);
		$request->request = new Parameters($data);

		return $request;
	}

	/**
	 * Creates a new request from a factory.
	 * 
	 * @param  array  $queryString
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return static
	 */
	protected static function createFromRequestFactory(
		array $queryString = [], 
		array $request = [], 
		array $cookies = [], 
		array $files = [], 
		array $server = []
	) {
		if (self::$requestURI) {
			$request = (self::$requestURI)($queryString, $request, $cookies, $files, $server);

			if ( ! $request instanceof self) {
				throw new LogicException('The Request active must return an instance of Syscodes\Components\Http\Request');
			}

			return $request;
		}

		return new static($queryString, $request, $cookies, $files, $server);
	}

	/**
	 * Clones a request and overrides some of its parameters.
	 * 
	 * @param  array  $queryString
	 * @param  array  $request
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return static
	 */
	public function duplicate(
		array $queryString = [], 
		array $request = [], 
		array $cookies = [],
		array $files = [],
		array $server = []
	) {
		$duplicate = clone $this;

		if (null !== $queryString) {
			$duplicate->queryString = new Inputs($queryString);
		}

		if (null !== $request) {
			$duplicate->request = new Inputs($request);
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
	 * @param  \Syscodes\Components\Http\Request|bool|null  $request  Overwrite current request 
	 *                                                      before returning, false prevents 
	 *                                                      overwrite
	 *
	 * @return \Syscodes\Components\Http\Request
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
	 * @return array|null
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
	public function detectLocale(): void
	{
		$this->languages = $this->defaultLocale = config('app.locale');

		$this->setLocale($this->validLocales);
	}

	/**
	 * Returns the default locale as set.
	 * 
	 * @return string
	 */
	public function getDefaultLocale(): string
	{
		return $this->defaultLocale;
	}

	/**
	 * Gets the current locale, with a fallback to the default.
	 * 
	 * @return string 
	 */
	public function getLocale(): string
	{
		return $this->languages ?: $this->defaultLocale;
	}

	/**
	 * Sets the locale string for this request.
	 * 
	 * @param  string  $locale
	 * 
	 * @return \Syscodes\Components\Http\Request
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
	 * @param  string  $key
	 * @param  mixed  $default
	 *
	 * @return mixed 
	 */
	public function get(string $key, $default = null) 
	{
		if ($this->queryString->has($key)) {
			return $this->queryString->all()[$key];
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
		$session = $this->session;
		
		if ( ! $session instanceof SessionInterface && null !== $session) {
			$this->setSession(new SessionDecorator($session));
		}
		
		if (null === $session) {
			throw new SessionNotFoundException('Session has not been set');
		}
		
		return $session;
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
	 * @return \Syscodes\Components\Http\Contributors\Parameters|mixed
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
	 * @param  \Syscodes\Components\Http\Contributors\Parameters  $json
	 * 
	 * @return self
	 */
	public function setJson($json): self
	{
		$this->json = $json;

		return $this;
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
	 * Get the input source for the request.
	 * 
	 * @return \Syscodes\Components\Http\Contributors\Parameters
	 */
	public function getInputSource()
	{
		if ($this->isJson()) {
			return $this->json();
		}

		return in_array($this->getMethod(), ['GET', 'HEAD']) ? $this->queryString : $this->request;
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
		if (($request = static::active())) {
			$path = trim($this->getPathInfo(), '/');
		}

		return $path == '' ? $request->uri->getPath().'/' : $path;
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
			$this->pathInfo = $this->http->parsePathInfo();
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
			$this->baseUrl = $this->http->parseBaseUrl();
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
			$this->requestToUri = $this->http->parseRequestUri();
		}

		return $this->requestToUri;
	}
	
	/**
	 * Gets the request's scheme.
	 * 
	 * @return string
	 */
	public function getScheme(): string
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
			$host = $forwardedHost[0];
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
	public function getPort(): int
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
	public function getHttpHost(): string
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
	public function getSchemeWithHttpHost(): string
	{
		return $this->getScheme().'://'.$this->getHttpHost();
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
		return trim(preg_replace('/\?.*/', '', $this->path()), '/');
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
	 * @return self
	 */
	public function setRouteResolver(Closure $callback): self
	{
		$this->routeResolver = $callback;

		return $this;
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
		return Arr::get($this->all(), $key, function () use ($key) {
			return $this->route($key);
		});
	}

	/**
	 * Magic method.
	 * 
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
	 * Magic method.
	 * 
	 * Clones the current request.
	 * 
	 * @return void
	 */
	public function __clone()
	{
		$this->queryString = clone $this->queryString;
		$this->request     = clone $this->request;
		$this->cookies     = clone $this->cookies;
		$this->files       = clone $this->files;
		$this->server      = clone $this->server;
		$this->headers     = clone $this->headers;
	}
}