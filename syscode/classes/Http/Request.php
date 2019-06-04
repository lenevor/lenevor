<?php

namespace Syscode\Http;

Use Locale;
use Exception;

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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
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
	 * @var string|null $method
	 */
	protected $method = null;

	/** 
	 * List of routes uri.
	 *
	 * @var array|null $uri 
	 */
	public $uri = null;

	/**
	 * Stores the valid locale codes.
	 * 
	 * @var array $validLocales
	 */
	protected $validLocales = [];

	/**
	 * Returns the active request currently being used.
	 *
	 * @param  \Syscode\Http\Request|bool|null  $request Overwrite current request before returning, false prevents overwrite
	 *
	 * @return \Syscode\Http\Request
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
	 * @param  int    $index  The segment number (1-based index)
	 * @param  mixed  $default  Default value to return
	 *
	 * @return  string
	 */
	public static function segment($index, $default = null)
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
	public static function segments()
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
	public static function totalSegments()
	{
		if ($request = self::active())
		{
			return $request->uri->getTotalSegments();
		}

		return null;
	}

	/**
	 * Constructor: Call uri.
	 * 
	 * @param  \Syscode\Http\Uri   $uri
	 * @param  \Syscode\Http\Http  $http
	 * 
	 * @return string
	 *
	 * @uses   \Syscode\Http\Server($_SERVER)	 
	 */
	public function __construct(Uri $uri, Http $http, Server $server)
	{
		static::$active     = $this;
		$this->uri          = $uri;
		$this->http         = $http;
		$this->validLocales = config('app.supportedLocales');
		$this->method       = $server->get('REQUEST_METHOD') ?? 'GET';

		$this->detectLocale();
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
	 * Returns the full request string.
	 *
	 * @return string  The Request string
	 */
	public function get() 
	{
		if ($request = self::active())
		{
			return $request->uri->get();
		}

		return null;
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
	 * Contents of the Host: header from the current request, if there is one.
	 * 
	 * @return bool
	 */
	public function getHost()
	{
		return $this->http->server('HTTP_HOST');
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
	 * Returns the request method.
	 *
	 * @param  bool  $upper  Whether to return in upper or lower case
	 * 
	 * @return string  
	 */
	public function getMethod($upper = true)
	{
		return ($upper) ? strtoupper($this->method) : strtolower($this->method);
	}

	/**
	 * Return's whether this is an AJAX request or not.
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		return ($this->http->server('HTTP_X_REQUESTED_WITH') !== null) and strtolower($this->http->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Return's the input method used (GET, POST, DELETE, etc.).
	 *
	 * @param  string  $default
	 *
	 * @return string
	 */
	public function method($default = 'GET')
	{
		return $this->http->server('REQUEST_METHOD', $default);
	}

	/**
	 * Sets the locale string for this request.
	 * 
	 * @param  string  $locale
	 * 
	 * @return Request
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
	 * Sets the request method.
	 *
	 * @param  string  $method  
	 *
	 * @return object  $this
	 */
	public function setMethod($method) 
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Return's the user agent.
	 *
	 * @param  string  $default
	 *
	 * @return string
	 */
	public function userAgent($default = null)
	{
		return $this->http->server('HTTP_USER_AGENT', $default);
	}
}