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

namespace Syscodes\Components\Http\Resources;

use LogicException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Loaders\Inputs;

/**
 * Allows that HTTP request  loading to initialize the system.
 */
trait HttpRequest
{
	/**
	 * Holds the global active request instance.
	 *
	 * @var bool $requestURI
	 */
	protected static $requestURI;

	/**
	 * Get the http method parameter.
	 * 
	 * @var bool $httpMethodParameterOverride
	 */
	protected static $httpMethodParameterOverride = false;

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
}