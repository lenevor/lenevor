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
	public static function base($indexPage = true)
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
	public static function create($uri)
	{
		$url = '';
		is_null($uri) && $uri = Uri::get();

		// If the given uri is not a full URL
		if ( ! preg_match("#^(http|https|ftp)://#i", $uri))
		{
			$url .= config('baseUrl');

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
	public static function to($uri) 
	{
		if (strpos($uri, '://')) return $uri;

		$base = config('app.baseUrl');

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
	public static function full($uri, $secure = null) 
	{
		if (strpos($uri, '://')) return $uri;

		// create a server object from global
		$server = new Server($_SERVER);

		if ( ! is_null($secure)) 
		{
			$scheme = $secure ? 'https://' : 'http://';
		}
		else 
		{
			$scheme = ($server->has('HTTPS') and $server->get('HTTPS')) !== '' ? 'http://' : 'https://';
		}

		return $scheme . $server->get('HTTP_HOST').self::to($uri);
	}

	/**
	 * Constructor: Call Uri.
	 * 
	 * @param  string  $uri
	 *
	 * @return null|segment
	 * 
	 * @uses   \Syscode\Http\Http
	 */
	public function __construct($uri = null) 
	{
		is_object($uri) && $uri = null;

		$this->uri = trim($uri ?: Http::detectedUri(), '/');

		if (empty($this->uri))
		{
			$this->segments = [];
		}
		else
		{
			$this->segments = explode('/', $this->uri);
		}
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
	 * Get the specified URI segment, return default if it doesn't exist.
	 * Segment index is 1 based, not 0 based.
	 *
	 * @param  int    $index    The 1-based segment index
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public function getSegment($index, $default = null)
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