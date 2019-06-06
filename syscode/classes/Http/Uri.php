<?php    

namespace Syscode\Http;

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
class Uri
{
	/**
	 * The URI segments.
	 *
	 * @var array $segments
	 */
	protected $segments = null;

	/**
	 * The URI string.
	 *
	 * @var string $uri
	 */
	protected $uri = null;

	/**
	 * Gets the base URL, including the indexPage if wanted.
	 *
	 * @param  bool  $indexPage  Include index.php in the URL as optional
	 *
	 * @return string
	 */
	public static function base(bool $indexPage = true)
	{
		$url = config('baseUrl');

		if ($indexPage && config('indexPage'))
		{
			$url .= config('indexPage').'/';
		}

		return $url;
	}

	/**
	 * Creates a url with the given uri, including the base url.
	 *
	 * @param  string  $uri  The uri to create the URL
	 *
	 * @return string
	 */
	public static function create(string $uri)
	{
		$url = '';

		is_null($uri) && $uri = Uri::get();

		// If the given uri is not a full URL
		if ( ! preg_match("#^(http|https|ftp)://#i", $uri))
		{
			$url .= config('baseUrl').$_SERVER['REQUEST_URI'];

			if ($indexPage = config('indexPage'))
			{
				$url .= $indexPage.'/';
			}
		}

		$url .= ltrim($uri, '/');

		return $url;
	}

	/**
	 * Get a path relative to the application.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 */
	public static function to(string $uri) 
	{
		if (strpos($uri, '://')) return $uri;

		$base = config('app.baseUrl').$_SERVER['REQUEST_URI'];

		if ($index = config('app.indexPage')) 
		{
			$index .= '/';
		}

		return rtrim($base, '/').'/'.$index.ltrim($uri, '/');
	}

	/**
	 * Get full uri relative to the application.
	 *
	 * @param  string       $uri
	 * @param  string|null  $secure
	 *
	 * @return string
	 */
	public static function full(string $uri, $secure = null) 
	{
		if (strpos($uri, '://')) return $uri;

		// create a server object from global
		$server = new Parameter($_SERVER);

		if ( ! is_null($secure)) 
		{
			$scheme = $secure ? 'https://' : 'http://';
		}
		else 
		{
			$scheme = ($server->has('HTTPS') and $server->get('HTTPS')) !== '' ? 'http://' : 'https://';
		}

		return $scheme.$server->get('HTTP_HOST').self::to($uri);
	}

	/**
	 * Constructor: Initialize the Uri class.
	 * 
	 * @param  \Syscode\Http\Http  $http
	 *
	 * @return void
	 */
	public function __construct(Http $http) 
	{
		$this->uri = $uri = $http->detectedURI();

		$this->set($uri);

		$this->filterSegments($uri);
	} 

	/**
	 * Returns the full URI string.
	 *
	 * @return string  The URI string
	 */
	public function get()
	{
		return '/'.ltrim($this->uri, '/');
	}

	/**
	 * Sets of URI string.
	 * 
	 * @param  string  $uri
	 * 
	 * @return $this
	 */
	public function set(string $uri)
	{
		$this->uri = $uri;

		return $this;
	}

	/**
	 * Filter the segments of path.
	 * 
	 * @param  string  $uri
	 * 
	 * @return string[]
	 */
	protected function filterSegments(string $uri)
	{
		$this->segments = ( ! empty($uri) ? explode('/', $uri) : []);
	}

	/**
	 * Get the specified URI segment, return default if it doesn't exist.
	 * Segment index is 1 based, not 0 based.
	 *
	 * @param  int    $index    The 1-based segment index
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public function getSegment(int $index, $default = null)
	{
		return array_get($this->getSegments(), $index - 1, $default);
	}

	/**
	 * Returns the segments of the path as an array.
	 *
	 * @return array  The URI segments
	 */
	public function getSegments()
	{
		return array_values(array_filter($this->segments, function ($value) {
			return $value != '';
		}));
	}

	/**
	 * Returns the total number of segment.
	 *
	 * @return int  
	 */
	public function getTotalSegments()
	{
		return count($this->getSegments());
	}

	/**
	 * Returns the URI string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->get();
	}
}