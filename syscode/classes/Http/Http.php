<?php 

namespace Syscode\Http;

use OverflowException;
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
	public function detectedUri()
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
		  				return $this->formats($uri, $server);
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
	public function formats($uri, $server)
	{
		// Remove all characters except letters,
		// digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
		$uri = filter_var(rawurldecode($uri), FILTER_SANITIZE_URL);

		// Remove script path/name
		$uri = $this->removeScriptName($uri, $server);

		// Remove the relative uri
		$uri = $this->removeRelativeUri($uri);

		// Return argument if not empty or return a single slash
		return trim($uri, '/') ?: '/';
	}

	/**
	 * Return's whether this is an AJAX request or not.
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		return ($this->server('HTTP_X_REQUESTED_WITH') !== null) and strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Determines if this request was made from the command line (CLI).
	 * 
	 * @return bool
	 */
	public function isCli()
	{
		return (PHP_SAPI === 'cli' || defined('STDIN'));
	}

	/**
	 * Contents of the Host: header from the current request, if there is one.
	 * 
	 * @return bool
	 */
	public function isHost()
	{
		return $this->server('HTTP_HOST');
	}

	/**
	 * Attempts to detect if the current connection is secure through a few 
	 * different methods.
	 * 
	 * @return bool
	 */
	public function isSecure()
	{
		if ( ! empty($this->server('HTTPS')) && strtolower($this->server('HTTPS')) !== 'off')
		{
			return true;
		}
		elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $this->server('HTTP_X_FORWARDED_PROTO') === 'https')
		{
			return true;
		}
		elseif ( ! empty($this->server('HTTP_FRONT_END_HTTPS')) && strtolower($this->server('HTTP_FRONT_END_HTTPS')) !== 'off')
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
	public function method($default = 'GET')
	{
		return $this->server('REQUEST_METHOD', $default);
	}

	/**
	 * Return's the protocol that the request was made with.
	 *
	 * @return string
	 *
	 * @uses   \Config\Configure
	 */
	public function protocol()
	{
		if ($this->server('HTTPS') == 'on' ||
			$this->server('HTTPS') == 1 ||
			$this->server('SERVER_PORT') == 443 ||
			(Configure::get('security.allow-x-headers', false) && $this->server('HTTP_X_FORWARDED_PROTO') == 'https') ||
			(Configure::get('security.allow-x-headers', false) && $this->server('HTTP_X_FORWARDED_PORT') == 443))
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
	public function remove($value, $uri)
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
	public function removeScriptName($uri, $server)
	{
		return $this->remove($server->get('SCRIPT_NAME'), $uri);
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
	public function removeRelativeUri($uri) 
	{
		// remove base url
		if ($base = config('app.baseUrl')) 
		{
			$uri = $this->remove(rtrim($base, '/'), $uri);
		}

		// remove index
		if ($index = config('app.indexPage')) 
		{
			$uri = $this->remove('/'.$index, $uri);
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
	public function server($index = null, $default = null)
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
	public function userAgent($default = null)
	{
		return $this->server('HTTP_USER_AGENT', $default);
	}
}