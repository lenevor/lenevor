<?php 

namespace Syscode\Http;

use OverflowException;
use Syscode\Core\Http\Exceptions\BadRequestHttpException;

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
	 * @throws \Syscode\Core\Http\Exceptions\BadRequestHttpException
	 * @throws \OverflowException
	 */
	public function detectedURI()
	{
		$server = new Parameter($_SERVER);

		if ($server->has(config('app.uriProtocol')) && $uri = $server->get(config('app.uriProtocol')))
		{
			if ($uri = filter_var($uri, FILTER_SANITIZE_URL))
			{
				if ($uri = parse_url($uri, PHP_URL_PATH))
				{
					return $this->formats($uri, $server);
				}

				throw new BadRequestHttpException('Malformed URI');	  			
			}

			throw new OverflowException('Uri was not detected. Make sure the REQUEST_URI is set.');			    			    		
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

		// Filters a value from the start of a string
		$uri = $this->parseRequestURI($server->get('SCRIPT_NAME'), $uri);

		// Return argument if not empty or return a single slash
		return trim($uri, '/') ?: '/';
	}

	protected function detectPatch(string $protocol, $server, $uri) 
	{

	}

	/**
	 * Filters a value from the start of a string in this case the passed uri string.
	 *
	 * @param  string  $value
	 * @param  string  $uri
	 *
	 * @return string
	 */
	protected function parseRequestURI($value, $uri)
	{
		if ( ! isset($uri, $value))
		{
			return '';
		}

		$parts = parse_url('http://dummy'.$uri);
		$query = $parts['query'] ?? '';
		$uri   = $parts['path'] ?? '';

		// If the search value is at the start
		if (isset($value[0]))
		{
			if (strpos($uri, $value) === 0)
			{
				$uri = (string) substr($uri, strlen($value));
			}
			elseif (strpos($uri, $value) > 0)
			{
				$uri = (string) substr($uri, strpos($uri, $value) + strlen($value));
			}
			elseif (strpos($uri, dirname($value)) === 0)
			{
				$uri = (string) substr($uri, strlen(dirname($value)));
			}
		}

		// This section ensures that even on servers that require the URI to contain 
		// the query string (Nginx) is the correctly
		if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0) 
		{
			$query					 = explode('?', $query, 2);
			$uri  					 = $query[0];
			$_SERVER['QUERY_STRING'] = $query[1] ?? '';
		}
		else
		{
			$_SERVER['QUERY_STRING'] = $query;
		}

		// Parses the string into variables
		parse_str($_SERVER['QUERY_STRING'], $_GET);

		if ($uri === '/' || $uri === '')
		{
			return '/';
		}

		return $uri;
	}

	/**
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 * 
	 * @param  string  $server
	 * 
	 * @return string
	 */
	protected function parseQueryString($server)
	{
		$uri = $server ?? @getenv('QUERY_STRING');

		if (trim($uri, '/') === '')
		{
			return '';
		}
		elseif (strncmp($uri, '/', 1) === 0)
		{
			$uri    = explode('?', $uri, 2);
			$server = $uri[1] ?? '';
			$uri    = $uri[0] ?? '';
		}

		parse_str($server, $_GET);

		return $uri;
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
	 * Fetch an item from the COOKIE array.
	 *
	 * @param  string  $index    The index key
	 * @param  mixed   $default  The default value
	 *
	 * @return string|array
	 */
	public function cookie($index = null, $default = null)
	{
		return (func_num_args() === 0) ? $_COOKIE : array_get($_COOKIE, strtoupper($index), $default);
	}

	/**
	 * Fetch an item from the FILE array.
	 *
	 * @param  string  $index    The index key
	 * @param  mixed   $default  The default value
	 *
	 * @return string|array
	 */
	public function file($index = null, $default = null)
	{
		return (func_num_args() === 0) ? $_FILES : array_get($_FILES, strtoupper($index), $default);
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
}