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
class Http
{
	/**
	 * Gets the URIProtocol based setting, will attempt to detect the path 
	 * portion of the current URI.
	 * 
	 * @param  string  $protocol
	 * @param  string  $value
	 * 
	 * @return string
	 */
	public function detectPath(string $protocol = '') 
	{
		if (empty($protocol))
		{
			$protocol = 'REQUEST_URI';
		}

		switch($protocol)
		{
			case 'REQUEST_URI':
				$path = $this->parseRequestURI();
				break;
			case 'QUERY_STRING':
				$path = $this->parseQueryString();
				break;
			case 'PATH_INFO':
			default:
				$path = $this->server($protocol) ?? $this->parseRequestURI();
				break;
		}

		return $path;
	}

	/**
	 * Filters a value from the start of a string in this case the passed uri string.
	 *
	 * @return string
	 */
	protected function parseRequestURI()
	{
		if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']))
		{
			return '';
		}

		$parts = parse_url('http://dummy'.$_SERVER['REQUEST_URI']);
		$query = $parts['query'] ?? '';
		$uri   = $parts['path'] ?? '';

		// If the search value is at the start
		if (isset($_SERVER['SCRIPT_NAME'][0]))
		{
			if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
			{
				$uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			}
			elseif (strpos($uri, $_SERVER['SCRIPT_NAME']) > 0)
			{
				$uri = (string) substr($uri, strpos($uri, $_SERVER['SCRIPT_NAME']) + strlen($_SERVER['SCRIPT_NAME']));
			}
			elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
			{
				$uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
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

		return $this->filterDecode($uri);
	}

	/**
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 * 
	 * @return string
	 */
	protected function parseQueryString()
	{
		$uri = $_SERVER['QUERY_STRING'] ?? @getenv('QUERY_STRING');

		if (trim($uri, '/') === '')
		{
			return '';
		}
		elseif (strncmp($uri, '/', 1) === 0)
		{
			$uri    				 = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = $uri[1] ?? '';
			$uri    				 = $uri[0] ?? '';
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		return $this->filterDecode($uri);
	}

	/**
	 * Filters the uri string remove any malicious characters and inappropriate slashes.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 */
	public function filterDecode($uri)
	{
		// Remove all characters except letters,
		// digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
		$uri = filter_var(rawurldecode($uri), FILTER_SANITIZE_URL);

		// Return argument if not empty or return a single slash
		return trim($uri, '/') ?: '/';
	}
	
	/**
	 * Prepares the base URL.
	 * 
	 * @return string
	 */
	public function prepareBaseUrl() 
	{
		$filename = basename($this->server('SCRIPT_FILENAME'));
		
		if (basename($this->server('SCRIPT_NAME')) === $filename)
		{
			$baseUrl = $this->server('SCRIPT_NAME');
		}
		elseif (basename($this->server('PHP_SELF')) === $filename)
		{
			$baseUrl = $this->server('PHP_SELF');
		}
		elseif (basename($this->server('ORIG_SCRIPT_NAME')) === $filename)
		{
			$baseUrl = $this->server('ORIG_SCRIPT_NAME');
		}
		else
		{
			$path = $this->server('PHP_SELF', '');
			$file = $this->server('SCRIPT_FILENAME', '');
			$segs = explode('/', trim($file, '/'));
			$segs = array_reverse($segs);
			$index = 0;
			$last = count($segs);
			$baseUrl = '';
			
			do
			{
				$seg = $segs[$index];
				$baseUrl = '/'.$seg.$baseUrl;
				++$index;
			} while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
		}

		$baseUrl = dirname(($baseUrl));
		
		// Does the baseUrl have anything in common with the request_uri?
		$requestUri = $this->parseRequestURI();

		if ('' !== $requestUri && '/' !== $requestUri[0])
		{
			$requestUri = '/'.$requestUri;
		}
		
		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of baseUrl. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos)
		{
			$baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
		}
		
		return rtrim($baseUrl, '/'.DIRECTORY_SEPARATOR);
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
			(config('security.allow-x-headers', false) && $this->server('HTTP_X_FORWARDED_PROTO') == 'https') ||
			(config('security.allow-x-headers', false) && $this->server('HTTP_X_FORWARDED_PORT') == 443))
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