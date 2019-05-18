<?php 

namespace Syscode\Http;

use OverflowException;
use Syscode\Config\Configure;
use Syscode\Core\Exceptions\LenevorException;

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
class Http
{
	/**
	 * Detects and returns the current URI based on a number of different server variables.
	 *
	 * @return string
	 *
	 * @throws \Syscode\Core\Exceptions\LenevorException
	 * @throws \OverflowException
	 */
	public static function detectedUri()
	{
		$server = new Server($_SERVER);

		$vars = ['REQUEST_URI', 'PATH_INFO', 'ORIG_PATH_INFO'];

		foreach ($vars as $httpVar) 
		{
		  	if ($server->has($httpVar) && $uri = $server->get($httpVar))
		  	{
		  		if ($uri = filter_var($uri, FILTER_SANITIZE_URL))
		  		{
		  			if ($uri = parse_url($uri, PHP_URL_PATH))
		  			{
		  				return static::formats($uri, $server);
		  			}

		  			throw new LenevorException('Malformed URI');	  			
				}

				throw new OverflowException('Uri was not detected. Make sure the REQUEST_URI is set.');			    			    		
			}		
		}			    			    		
	}

	/**
	 * Format the uri string remove any malicious characters and relative paths.
	 *
	 * @param  string  $uri
	 * @param  string  $server
	 *
	 * @return string
	 */
	public static function formats($uri, $server)
	{
		// Remove all characters except letters,
		// digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
		$uri = filter_var(rawurldecode($uri), FILTER_SANITIZE_URL);

		// Remove script path/name
		$uri = static::removeScriptName($uri, $server);

		// Remove the relative uri
		$uri = static::removeRelativeUri($uri);

		// Return argument if not empty or return a single slash
		return trim($uri, '/') ?: '/';
	}

	/**
	 * Return's whether this is an AJAX request or not.
	 *
	 * @return bool
	 */
	public static function isAjax()
	{
		return (static::server('HTTP_X_REQUESTED_WITH') !== null) and strtolower(static::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Determines if this request was made from the command line (CLI).
	 * 
	 * @return bool
	 */
	public static function isCli()
	{
		return (PHP_SAPI === 'cli' || defined('STDIN'));
	}

	/**
	 * Contents of the Host: header from the current request, if there is one.
	 * 
	 * @return bool
	 */
	public static function isHost()
	{
		return static::server('HTTP_HOST');
	}

	/**
	 * Attempts to detect if the current connection is secure through a few 
	 * different methods.
	 * 
	 * @return bool
	 */
	public static function isSecure()
	{
		if ( ! empty(static::server('HTTPS')) && strtolower(static::server('HTTPS')) !== 'off')
		{
			return true;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && static::server('HTTP_X_FORWARDED_PROTO') === 'https')
		{
			return true;
		}
		elseif ( ! empty(static::server('HTTP_FRONT_END_HTTPS')) && strtolower(static::server('HTTP_FRONT_END_HTTPS')) !== 'off')
		{
			return true;
		}

		return false;
	}

	/**
	 * Return's the input method used (GET, POST, DELETE, etc.).
	 *
	 * @param  string  $default
	 *
	 * @return string
	 */
	public static function method($default = 'GET')
	{
		return static::server('REQUEST_METHOD', $default);
	}

	/**
	 * Return's the protocol that the request was made with.
	 *
	 * @return string
	 *
	 * @uses   \Config\Configure
	 */
	public static function protocol()
	{
		if (static::server('HTTPS') == 'on' ||
			static::server('HTTPS') == 1 ||
			static::server('SERVER_PORT') == 443 ||
			(Configure::get('security.allow-x-headers', false) && static::server('HTTP_X_FORWARDED_PROTO') == 'https') ||
			(Configure::get('security.allow-x-headers', false) && static::server('HTTP_X_FORWARDED_PORT') == 443))
		{
			return 'https';
		}

		return 'http';
	}

	/**
	 * Remove a value from the start of a string in this case the passed uri string.
	 *
	 * @param  string  $value
	 * @param  string  $uri
	 *
	 * @return string
	 */
	public static function remove($value, $uri)
	{
		// make sure our search value is a non-empty string
		if (is_string($value) && strlen($value))
		{
			// If the search value is at the start
			if (strpos($uri, $value) === 0)
			{
				$uri = substr($uri, strlen($value));
			}
		}

		return $uri;
	}

	/**
	 * Remove the SCRIPT_NAME from the uri path.
	 *
	 * @param  string  $uri
	 * @param  string  $server
	 *
	 * @return string
	 */
	public static function removeScriptName($uri, $server)
	{
		return static::remove($server->get('SCRIPT_NAME'), $uri);
	}

	/**
	 * Remove the relative path from the uri set in the config folder.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 *
	 * @uses   \Config\Configure
	 */
	public static function removeRelativeUri($uri) 
	{
		// remove base url
		if ($base = config('app.baseUrl')) 
		{
			$uri = static::remove(rtrim($base, '/'), $uri);
		}

		// remove index
		if ($index = config('app.indexPage')) 
		{
			$uri = static::remove('/'.$index, $uri);
		}

		return $uri;
	}

	/**
	 * Fetch an item from the SERVER array.
	 *
	 * @param  string  $index    The index key
	 * @param  mixed   $default  The default value
	 *
	 * @return string|array
	 */
	public static function server($index = null, $default = null)
	{
		return (func_num_args() === 0) ? $_SERVER : array_get($_SERVER, strtoupper($index), $default);
	}

	/**
	 * Return's the user agent.
	 *
	 * @param  string  $default
	 *
	 * @return string
	 */
	public static function userAgent($default = null)
	{
		return static::server('HTTP_USER_AGENT', $default);
	}
}