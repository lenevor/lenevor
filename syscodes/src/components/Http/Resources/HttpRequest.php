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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Resources;

use Locale;
use LogicException;
use Syscodes\Components\Http\URI;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Loaders\Files;
use Syscodes\Components\Http\Loaders\Inputs;
use Syscodes\Components\Http\Loaders\Server;
use Syscodes\Components\Http\Loaders\Headers;
use Syscodes\Components\Http\Loaders\Parameters;
use Syscodes\Components\Http\Helpers\RequestClientIP;

class_exists(Files::class);
class_exists(Inputs::class);
class_exists(Server::class);
class_exists(Headers::class);
class_exists(Parameters::class);

/**
 * Allows that HTTP request  loading to initialize the system.
 */
trait HttpRequest
{
	/**
	 * Get the http method parameter.
	 * 
	 * @var bool $httpMethodParameterOverride
	 */
	protected static $httpMethodParameterOverride = false;

	/**
	 * Holds the global active request instance.
	 *
	 * @var bool $requestURI
	 */
	protected static $requestURI;

	/**
	 * Get the custom parameters.
	 * 
	 * @var \Syscodes\Components\Http\Loaders\Parameters $attributes
	 */
	public $attributes;

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
	 * @var \Syscodes\Components\Http\Loaders\Inputs $cookies
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
	 * @var \Syscodes\Components\Http\Loaders\Files $files
	 */
	public $files;
	
	/**
	 * Get the headers request ($_SERVER).
	 * 
	 * @var \Syscodes\Components\Http\Loaders\Headers $headers
	 */
	public $headers;

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
	 * Query string parameters ($_GET).
	 * 
	 * @var \Syscodes\Components\Http\Loaders\Parameters $query
	 */
	public $query;

	/**
	 * Request body parameters ($_POST).
	 * 
	 * @var \Syscodes\Components\Http\Loaders\Parameters $request
	 */
	public $request;

	/**
	 * The detected uri and server variables ($_SERVER).
	 * 
	 * @var \Syscodes\Components\Http\Loaders\Server $server
	 */
	public $server;

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
	 * Creates an Syscodes request from of the Request class instance.
	 * 
	 * @param  \Syscodes\Components\Http\Request  $request
	 * 
	 * @return static
	 */
	public static function createFromRequest($request): static
	{
		$newRequest = (new static)->duplicate(
			$request->query->all(), $request->request->all(), $request->attributes->all(),
			$request->cookies->all(), $request->files->all(), $request->server->all()
		);
		
		$newRequest->headers->replace($request->headers->all());
		
		$newRequest->content = $request->content;
		
		if ($newRequest->isJson()) {
			$newRequest->request = $newRequest->json();
		}
		
		return $newRequest;
	}

	/**
	 * Creates a new request with value from PHP's super global.
	 * 
	 * @return static
	 */
	public static function createFromRequestGlobals(): static
	{
		$request = static::createFromRequestFactory($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);

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
		array $server = []
	): static {
		if (self::$requestURI) {
			$request = (self::$requestURI)($query, $request, [], $cookies, $files, $server);

			if ( ! $request instanceof self) {
				throw new LogicException('The Request active must return an instance of Syscodes\Components\Http\Request');
			}

			return $request;
		}

		return new static($query, $request, $attributes, $cookies, $files, $server);
	}

	/**
	 * Returns the factory request currently being used.
	 *
	 * @param  \Syscodes\Components\Http\Request|callable|null  $request  
	 *
	 * @return void
	 */
	public static function setFactory(?callable $request): void
	{
		self::$requestURI = $request;
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
		$this->content = $content;
		$this->pathInfo = null;
		$this->languages = null;
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

		$this->setLocale($this->validLocales[0]);
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
}