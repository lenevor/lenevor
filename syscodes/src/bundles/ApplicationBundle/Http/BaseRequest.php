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

namespace Syscodes\Bundles\ApplicationBundle\Http;

use Locale;
use Closure;
use LogicException;
use Syscodes\Components\Http\URI;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Helpers\RequestClientIP;
use Syscodes\Components\Http\Session\SessionInterface;
use Syscodes\Bundles\ApplicationBundle\Http\Loaders\Files;
use Syscodes\Bundles\ApplicationBundle\Http\Loaders\Inputs;
use Syscodes\Bundles\ApplicationBundle\Http\Loaders\Server;
use Syscodes\Bundles\ApplicationBundle\Http\Loaders\Headers;
use Syscodes\Bundles\ApplicationBundle\Http\Loaders\Parameters;
use Syscodes\Bundles\ApplicationBundle\Http\Helpers\RequestUtils;
use Syscodes\Bundles\ApplicationBundle\Http\Concerns\HttpResources;

class_exists(Files::class);
class_exists(Inputs::class);
class_exists(Server::class);
class_exists(Headers::class);
class_exists(Parameters::class);

/**
 * Allows that HTTP request  loading to initialize the system.
 */
class BaseRequest
{
	use HttpResources;
	/**
	 * Get the http method parameter.
	 * 
	 * @var bool $httpMethodParameterOverride
	 */
	protected static $httpMethodParameterOverride = false;

	/**
	 * Holds the global active request instance.
	 *
	 * @var \Closure $requestToUri
	 */
	protected static ?Closure $requestToUri = null;

	/**
	 * Get the acceptable of content types.
	 * 
	 * @var string[] $acceptableContenTypes
	 */
	protected $acceptableContentTypes;

	/**
	 * Get the custom parameters.
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Parameters $attributes
	 */
	public Parameters $attributes;

	/**
	 * The base URL.
	 * 
	 * @var string $baseUrl
	 */
	protected $baseUrl;

	/**
	 * Get the client ip.
	 * 
	 * @var mixed $clientIp
	 */
	protected $clientIp;

	/**
	 * Gets cookies ($_COOKIE).
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Inputs $cookies
	 */
	public Inputs $cookies;

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
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Files $files
	 */
	public Files $files;
	
	/**
	 * Get the headers request ($_SERVER).
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Headers $headers
	 */
	public Headers $headers;

	/**
	 * The current language of the application.
	 * 
	 * @var string $languages
	 */
	protected $languages;
	
	/**
	 * Get the locale.
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
	 * Query string parameters ($_GET).
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Inputs $query
	 */
	public Inputs $query;

	/**
	 * Request body parameters ($_POST).
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Inputs $request
	 */
	public Inputs $request;

	/**
	 * The Session implementation.
	 * 
	 * @var \Syscodes\Components\Http\Session\SessionInterface|Closure|null $session
	 */
	protected SessionInterface|Closure|null $session = null;

	/**
	 * The detected uri and server variables ($_SERVER).
	 * 
	 * @var \Syscodes\Bundles\ApplicationBundle\Http\Loaders\Server $server
	 */
	public Server $server;

	/**
	 * Get the request uri.
	 * 
	 * @var string|null $requestUri
	 */
	protected ?string $requestUri = null;

	/** 
	 * List of routes uri.
	 *
	 * @var \Syscodes\Components\Http\URI $uri 
	 */
	public $uri;

	/**
	 * Stores the valid locale codes.
	 * 
	 * @var array $validLocales
	 */
	protected $validLocales = [];

	/**
     * Enables support for the _method request parameter to determine the intended HTTP method.
     * 
     * @return void
     */
    public static function enabledHttpMethodParameterOverride(): void
    {
        self::$httpMethodParameterOverride = true;
    }
	
	/**
	 * Checks whether support for the _method request parameter is enabled.
	 * 
	 * @return bool
	 */
	public static function getHttpMethodParameterOverride(): bool
	{
		return self::$httpMethodParameterOverride;
	}

	/**
	 * Constructor. Create new the Request class.
	 * 
	 * @param  array  $query
	 * @param  array  $request
	 * @param  array  $attributes
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * @param  string|resource|null $content  
	 * 
	 * @return void
	 */
	public function __construct(
		array $query = [],
		array $request = [],
		array $attributes = [],
		array $cookies = [],
		array $files = [],
		array $server = [],
		$content = null
	) {
		$this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
		
		$this->detectLocale();
	}

	/**
	 * Creates a new request with value from PHP's super global.
	 * 
	 * @return static
	 */
	public static function createFromRequestGlobals(): static
	{
		$request = static::createFromRequestFactory($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);

		if ( ! in_array($request->server->get('REQUEST_METHOD', 'GET'), ['PUT', 'DELETE', 'PATCH', 'QUERY'], true)) {
            return $request;
        }

		if (Str::startsWith($request->headers->get('CONTENT_TYPE', ''), 'application/x-www-form-urlencoded')
		    && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])) {
			parse_str($request->getContent(), $data);
			$request->request = new Inputs($data);
		}

		return $request;
	}

	/**
	 * Creates a new request from a factory.
	 * 
	 * @param  array  $query
	 * @param  array  $request
	 * @param  array  $attributes
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return static
	 */
	private static function createFromRequestFactory(
		array $query = [], 
		array $request = [],
		array $attributes = [] ,
		array $cookies = [], 
		array $files = [], 
		array $server = [],
		?string $content = null 
	): static {
		if (self::$requestToUri) {
			$request = (self::$requestToUri)($query, $request, [], $cookies, $files, $server, $content = null);

			if ( ! $request instanceof self) {
				throw new LogicException('The Request active must return an instance of Syscodes\Bundles\ApplicationBundle\Http.');
			}

			return $request;
		}
		
		return new static($query, $request, $attributes, $cookies, $files, $server, $content);
	}

	/**
	 * Returns the factory request currently being used.
	 *
	 * @param  \Syscodes\Bundles\ApplicationBundle\Http\BaseRequest|callable|null  $request  
	 *
	 * @return void
	 */
	public static function setFactory(?callable $request): void
	{
		self::$requestToUri = $request;
	}

	/**
	 * Sets the parameters for this request.
	 * 
	 * @param  array  $query
	 * @param  array  $request
	 * @param  array  $attributes
	 * @param  array  $cookies
	 * @param  array  $files
	 * @param  array  $server
	 * 
	 * @return void
	 */
	public function initialize(
		array $query = [], 
		array $request = [],
		array $attributes = [],
		array $cookies = [], 
		array $files = [], 
		array $server = [], 
		$content = null
	): void {
		$this->query = new Inputs($query);
		$this->request = new Inputs($request);
		$this->attributes = new Parameters($attributes);
		$this->cookies = new Inputs($cookies);
		$this->files = new Files($files);
		$this->server = new Server($server);
		$this->headers = new Headers($this->server->all());

		// Variables initialized
		$this->uri = new URI;
		$this->method = null;
		$this->baseUrl = null;
		$this->pathInfo = null;
		$this->languages = null;
		$this->content = $content;
		$this->acceptableContentTypes = null;
		$this->validLocales = config('app.supportedLocales');
		$this->clientIp = new RequestClientIP($this->server->all());
	}

	/**
	 * Clones a request and overrides some of its parameters.
	 * 
	 * @param  array|null  $query
	 * @param  array|null  $request
	 * @param  array|null  $attributes
	 * @param  array|null  $cookies
	 * @param  array|null  $files
	 * @param  array|null  $server
	 * 
	 * @return static
	 */
	public function duplicate(
		?array $query = null, 
		?array $request = null,
		?array $attributes = null,
		?array $cookies = null,
		?array $files = null,
		?array $server = null
	): static {
		$duplicate = clone $this;

		if (null !== $query) {
			$duplicate->query = new Inputs($query);
		}

		if (null !== $request) {
			$duplicate->request = new Inputs($request);
		}

		if (null !== $attributes) {
			$duplicate->attributes = new Parameters($attributes);
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

		$duplicate->uri = new URI;
		$duplicate->locale = null;
		$duplicate->method = null;
		$duplicate->baseUrl = null;
		$duplicate->pathInfo = null;
		$duplicate->validLocales = config('app.supportedLocales');
		$duplicate->clientIp = new RequestClientIP($duplicate->server->all());

		return $duplicate;		
	}

	/**
	 * Handles setting up the locale, auto-detecting of language.
	 * 
	 * @return void
	 */
	public function detectLocale(): void
	{
		$this->languages = $this->defaultLocale = config('app.locale');

		$this->setLocale((string) $this->validLocales[0]);
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
	 * @return static
	 */
	public function setLocale(string $locale): static
	{
		if ( ! in_array($locale, $this->validLocales, true)) {
			$locale = $this->defaultLocale;
		}
		
		$this->languages = $locale;

		Locale::setDefault($locale);
			
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
	public function get(string $key, $default = null): mixed
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
	 * Returns the host name.
	 * 
	 * @return string
	 */
	public function getHost(): string
	{
		if ($forwardedHost = $this->server->get('HTTP_X_FORWARDED_HOST')) {
			$host = $forwardedHost[0];
		} elseif ( ! $host = $this->headers->get('HOST')) {
			if ( ! $host = $this->server->get('SERVER_NAME')) {
				$host = $this->server->get('REMOTE_ADDR', '');
			}
		}

		$host = strtolower(preg_replace('/:\d+$/', '', trim(($host))));
		
		return $this->uri->setHost($host);
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
	 * Gets the request's scheme.
	 * 
	 * @return string
	 */
	public function getScheme(): string
	{
		return $this->secure() ? $this->uri->setScheme('https') : $this->uri->setScheme('http');
	}

	/**
	 * Get the user.
	 * 
	 * @return string|null
	 */
	public function getUser(): ?string
	{
		$user = $this->uri->setUser(
			$this->headers->get('PHP_AUTH_USER')
		);

		return $user;
	}

	/**
	 * Get the password.
	 * 
	 * @return string|null
	 */
	public function getPassword(): ?string
	{
		$password = $this->uri->setPassword(
			$this->headers->get('PHP_AUTH_PW')
		);

		return $password;
	}

	/**
	 * Gets the user info.
	 * 
	 * @return string|null
	 */
	public function getUserInfo(): ?string
	{
		return $this->uri->getUserInfo();
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
	 * Generates the normalized query string for the Request.
	 * 
	 * @return string
	 */
	public function getQueryString(): string|null
	{
		$queryString = static::normalizedQueryString($this->server->get('QUERY_STRING'));
		
		return '' === $queryString ? null : $queryString;
	}

	/**
	 * Returns the path being requested relative to the executed script. 
	 * 
	 * @return string
	 */
	public function getPathInfo(): string
	{
		return $this->pathInfo ??= $this->parsePathInfo();
	}

	/**
	 * Returns the root URL from which this request is executed.
	 * 
	 * @return string
	 */
	public function getBaseUrl(): string
	{
		return $this->baseUrl ??= $this->parseBaseUrl();
	}

	/**
	 * Returns the requested URI.
	 * 
	 * @return string
	 */
	public function getRequestUri(): string
	{
		return $this->requestUri ??= $this->parseRequestUri();
	}

	/**
     * Normalizes a query string.
     * 
     * @param  string  $query
     * 
     * @return string
     */
    public static function normalizedQueryString(?string $query): string
    {
        if ('' === ($query ?? '')) {
            return '';
        }

        $query = RequestUtils::parseQuery($query);

        ksort($query);

        return http_build_query($query, '', '&', \PHP_QUERY_RFC3986);
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
	 * Retunrs the request body content.
	 * 
	 * @param  bool  $asResource
	 * 
	 * @return string
	 */
	public function getContent(bool $asResource = false): string
	{
		$currentContentIsResource = is_resource($this->content);

        if (true === $asResource) {
            if ($currentContentIsResource) {
                rewind($this->content);

                return $this->content;
            }

            if (is_string($this->content)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);

                return $resource;
            }

            $this->content = false;

            return fopen('php://input', 'r');
        }

        if ($currentContentIsResource) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
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