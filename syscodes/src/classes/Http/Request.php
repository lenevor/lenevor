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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.2
 */

namespace Syscodes\Http;

Use Locale;
use Exception;
use LogicException;
use Syscodes\Support\Str;
use Syscodes\Http\Contributors\Server;

/**
 * Request represents an HTTP request.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Request
{
	/**
	 * Holds the global active request instance.
	 *
	 * @var bool $active
	 */
	protected static $active = false;

	/**
	 * The base URL.
	 * 
	 * @var string $baseUrl
	 */
	protected $baseUrl;

	/**
	 * Gets the string with format JSON.
	 * 
	 * @var string $body 
	 */
	protected $body;

	/**
	 * The default Locale this request.
	 * 
	 * @var string $defaultLocale
	 */
	protected $defaultLocale;

	/**
	 * The detected uri and server variables.
	 * 
	 * @var string $http
	 */
	protected $http;

	/**
	 * The current Locale of the application.
	 * 
	 * @var string $locale
	 */
	protected $locale;
	
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
	 * The detected uri and server variables.
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
	 * @param  array  $server
	 * 
	 * @return void
	 */
	public function __construct(array $server = [])
	{
		static::$active = $this;
		
		$this->initialize($server);

		$this->detectURI(config('app.uriProtocol'), config('app.baseUrl'));

		$this->detectLocale();
	}

	/**
	 * Sets the parameters for this request.
	 * 
	 * @param  array  $server
	 * 
	 * @return void
	 */
	public function initialize(array $server = [])
	{
		$this->uri          = new URI;
		$this->http         = new Http;
		$this->locale       = null;
		$this->server       = new Server($server);
		$this->method       = null;
		$this->baseUrl      = null;
		$this->pathInfo     = null;
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
		$newRequest = (new static)->duplicate($request->server->all());

		return $newRequest;
	}

	/**
	 * Creates a new request with value from PHP's super global.
	 * 
	 * @return static
	 */
	public static function createFromRequestGlobals()
	{
		$request = static::createFromRequestFactory($_SERVER);

		return $request;
	}

	/**
	 * Creates a new request from a factory.
	 * 
	 * @param  array  $server
	 * 
	 * @return static
	 */
	protected static function createFromRequestFactory(array $server = [])
	{
		if (self::$active)
		{
			$request = (self::$active)($server);

			if ( ! $request instanceof self)
			{
				throw new LogicException('The Request active must return an instance of Syscodes\Http\Request');
			}

			return $request;
		}

		return new static($server);
	}

	/**
	 * Clones a request and overrides some of its parameters.
	 * 
	 * @param  array  $server
	 * 
	 * @return static
	 */
	public function duplicate(array $server = [])
	{
		$duplicate = clone $this;

		if (null !== $server)
		{
			$duplicate->server = new Server($server);
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
	 * @param  \Syscodes\Http\Request|bool|null  $request Overwrite current request before returning, false prevents overwrite
	 *
	 * @return \Syscodes\Http\Request
	 */
	public static function active($request = false)
	{
		if ($request !== false)
		{
			static::$active = $request;
		}

		return static::$active;
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
		if ($request = self::active())
		{
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
		if ($request = self::active())
		{
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
		if ($request = self::active())
		{
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

		if ( ! empty($baseUrl))
		{
			$this->uri->setScheme(parse_url($baseUrl, PHP_URL_SCHEME));
			$this->uri->setHost(parse_url($baseUrl, PHP_URL_HOST));
			$this->uri->setPort(parse_url($baseUrl, PHP_URL_PORT));
		}
		else 
		{
			if ( ! $this->http->isCli())
			{
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
		$this->locale = $this->defaultLocale = config('app.locale');

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
		return $this->locale ?: $this->defaultLocale;
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
		if ( ! in_array($locale, $this->validLocales))
		{
			$locale = $this->defaultLocale;
		}
		
		$this->locale = $locale;

		try
		{
		    if (class_exists('Locale', false))
			{
				Locale::setDefault($locale);
			}
		}
		catch (Exception $exception) {}

		return $this;
	}

	/**
	 * Returns the full request string.
	 *
	 * @return string|null  The Request string
	 */
	public function getUri() 
	{
		if ($request = self::active())
		{
			return $request->uri->get();
		}

		return null;
	}

	/**
	 * A convenience method that grabs the raw input stream and decodes
	 * the JSON into an array.
	 * 
	 * @param  bool  $assoc
	 * @param  int  $depth
	 * @param  int  $options
	 * 
	 * @return mixed
	 */
	public function getJSON(bool $assoc = false, int $depth = 512, int $options = 0)
	{
		return json_decode($this->body, $assoc, $depth, $options);
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
	public function method()
	{
		if (null !== $this->method)
		{
			return $this->method;
		}
		
		$this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
		
		if (in_array($this->method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true))
		{
			return $this->method;
		}
				
		throw new logicException(sprintf('Invalid method override "%s"', $this->method));
	}

	/**
	 * Sets the request method.
	 *
	 * @param  string  $method  
	 *
	 * @return $this
	 */
	public function setMethod(string $method) 
	{
		$this->method = null;

		$this->server->set('REQUEST_METHOD', $method);

		return $this;
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
		
		foreach ($patterns as $pattern)
		{
			if (Str::is($pattern, $path))
			{
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
	 * @param  string|null  $param  (null by default)
	 * @param  mixed  $default
	 * 
	 * @return \Syscodes\Routing\Route|object|string|null
	 */
	public function route($param = null, $default = null)
	{
		$route = $this->getRoute();

		if (is_null($route) || is_null($param))
		{
			return $route;
		}

		return $route->parameter($param, $default);
	}

	/**
	 * Returns the root URL from which this request is executed.
	 * 
	 * @return string
	 */
	public function getBaseUrl()
	{
		if (null === $this->baseUrl)
		{
			$this->baseUrl = $this->http->parseBaseUrl();
		}

		return $this->baseUrl;
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
	 * Returns the path being requested relative to the executed script. 
	 * 
	 * @return string
	 */
	public function getPathInfo()
	{
		if (null === $this->pathInfo)
		{
			$this->pathInfo = $this->http->parsePathInfo();
		}

		return $this->pathInfo;
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
		if ( ! $host = $this->server->get('SERVER_NAME'))
		{
			$host = $this->server->get('SERVER_ADDR', '');
		}

		$host = strtolower(preg_replace('/:\d+$/', '', $this->uri->setHost($host)));

		return $host;
	}

	/**
	 * Returns the port on which the request is made.
	 * 
	 * @return int
	 */
	public function getPort()
	{
		if ( ! $this->server->get('HTTP_HOST')) 
		{
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
		$host   = $this->getHost();
		$port   = $this->getPort();

		if (('http' === $scheme && 80 === $port) || ('https' === $scheme && 443 === $port))		
		{
			return $host;
		}

		return $host.':'.$port;
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
		return trim(preg_replace('/\?.*/', '', $this->getUri()), '/');
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
		if ( ! empty($this->server->get('HTTPS')) && strtolower($this->server->get('HTTPS')) !== 'off')
		{
			return true;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $this->server->get('HTTP_X_FORWARDED_PROTO') === 'https')
		{
			return true;
		}
		elseif ( ! empty($this->server->get('HTTP_FRONT_END_HTTPS')) && strtolower($this->server->get('HTTP_FRONT_END_HTTPS')) !== 'off')
		{
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
	 * Get the route resolver.
	 * 
	 * @return \Syscodes\Routing\Router
	 */
	public function getRoute()
	{
		return app('router');
	}

	/**
	 * Get an element from the request.
	 * 
	 * @return string[]
	 */
	public function __get($key)
	{
		$all = $this->server->all();

		if (array_key_exists($key, $all))
		{
			return $all[$key];
		}
		else
		{
			return $key;
		}
	}
}