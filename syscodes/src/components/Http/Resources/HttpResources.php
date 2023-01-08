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

/**
 * Returns the HTTP requests is filtered and detected in the routes set by the user.
 */
trait HttpResources
{
	/**
	 * Return's the protocol that the request was made with.
	 *
	 * @return string
	 */
	public function protocol(): string
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
	 * Gets the URI Protocol based setting, will attempt to detect the path 
	 * portion of the current URI.
	 * 
	 * @param  string  $protocol
	 * 
	 * @return string
	 */
	public function detectPath(string $protocol = ''): string
	{
		if (empty($protocol)) {
			$protocol = 'REQUEST_URI';
		}

		return match ($protocol) {
			'REQUEST_URI' => $this->parseRequestUri(),
			'QUERY_STRING' => $this->parseQueryString(),
			'PATH_INFO' => $this->server($protocol) ?? $this->parseRequestUri(),
			default => $this->parseRequestUri(),
		};
	}

	/**
	 * Filters a value from the start of a string in this case the passed URI string.
	 *
	 * @return string
	 */
	protected function parseRequestUri(): string
	{
		if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
			return '';
		}

		$requestUri = $this->server('REQUEST_URI') ?? '/';
		$components = parse_url($requestUri);
		$query      = $components['query'] ?? '';
		$uri        = $components['path'] ?? '';

		// If the search value is at the start
		if (isset($this->server('SCRIPT_NAME')[0])) {
			if (0 === strpos($uri, $this->server('SCRIPT_NAME'))) {
				$uri = (string) substr($uri, strlen($this->server('SCRIPT_NAME')));
			} elseif (0 < strpos($uri, $this->server('SCRIPT_NAME'))) {
				$uri = (string) substr($uri, strpos($uri, $this->server('SCRIPT_NAME')) + strlen($this->server('SCRIPT_NAME')));
			} elseif (0 === strpos($uri, dirname($this->server('SCRIPT_NAME')))) {
				$uri = (string) substr($uri, strlen(dirname($this->server('SCRIPT_NAME'))));
			}
		}

		// This section ensures that even on servers that require the URI to contain 
		// the query string (Nginx) is the correctly
		if ('' === trim($uri, '/') && 0 === strncmp($query, '/', 1)) {
			$query					 = explode('?', $query, 2);
			$uri  					 = $query[0];
			$_SERVER['QUERY_STRING'] = $query[1] ?? '';
		} else {
			$_SERVER['QUERY_STRING'] = $query;
		}

		// Parses the string into variables
		parse_str($_SERVER['QUERY_STRING'], $_GET);

		if ('/' === $uri || '' === $uri) {
			return '';
		}

		return $this->filterDecode($uri);
	}

	/**
	 * Will parse QUERY_STRING and automatically detect the URI from it.
	 * 
	 * @return string
	 */
	protected function parseQueryString(): string
	{
		$uri = $_SERVER['QUERY_STRING'] ?? @getenv('QUERY_STRING');

		if (trim($uri, '/') === '') {
			return '';
		} elseif (0 === strncmp($uri, '/', 1)) {
			$uri    				 = explode('?', $uri, 2);
			$_SERVER['QUERY_STRING'] = $uri[1] ?? '';
			$uri    				 = $uri[0] ?? '';
		}

		parse_str($_SERVER['QUERY_STRING'], $_GET);

		return $this->filterDecode($uri);
	}
	
	/**
	 * Parse the base URL.
	 * 
	 * @return string
	 */
	public function parseBaseUrl(): string
	{
		$filename = basename($this->server('SCRIPT_FILENAME'));
		
		if ($filename === basename($this->server('SCRIPT_NAME'))) {
			$baseUrl = $this->server('SCRIPT_NAME');
		} elseif ($filename === basename($this->server('PHP_SELF'))) {
			$baseUrl = $this->server('PHP_SELF');
		} elseif ($filename === basename($this->server('ORIG_SCRIPT_NAME'))) {
			$baseUrl = $this->server('ORIG_SCRIPT_NAME');
		} else {
			$path    = $this->server('PHP_SELF', '');
			$file    = $this->server('SCRIPT_NAME', '');
			$segs    = explode('/', trim($file, '/'));
			$segs    = array_reverse($segs);
			$index   = 0;
			$last    = count($segs);
			$baseUrl = '';
			
			do 	{
				$seg     = $segs[$index];
				$baseUrl = '/'.$seg.$baseUrl;
				++$index;
			} while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
		}

		// Does the baseUrl have anything in common with the request_uri?
		$requestUri = $this->parseRequestUri();

		if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/'.$requestUri;
        }

		$baseUrl = dirname($baseUrl ?? '');
		
		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of baseUrl. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && 0 !== $pos) {
			$baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
		}
		
		return $this->filterDecode($baseUrl);
	}

	/**
	 * Filters the uri string remove any malicious characters and inappropriate slashes.
	 *
	 * @param  string  $uri
	 *
	 * @return string
	 */
	protected function filterDecode($uri): string
	{
		// Remove all characters except letters,
		// digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
		$uri = filter_var(rawurldecode($uri), FILTER_SANITIZE_URL);
		
		// Return argument if not empty or return a single slash
		return rtrim($uri, '/'.DIRECTORY_SEPARATOR);
	}

	/**
	 * Parse the path info.
	 * 
	 * @return string
	 */
	public function parsePathInfo(): string
	{
		if (null === ($requestUri = $this->parseRequestUri())) {
			return '/';
		}

		// Remove the query string from REQUEST_URI
		if (false !== $pos = strpos($requestUri, '?')) {
			$requestUri = substr($requestUri, 0, $pos);
		}

		if ('' !== $requestUri && '/' !== $requestUri[0]) {
			$requestUri = '/'.$requestUri;
		}

		if (null === ($baseUrl = $this->parseBaseUrl())) {
			return $requestUri;
		}

		$pathInfo = substr($requestUri, strlen($baseUrl));

		if (false === $pathInfo || '' === $pathInfo) {
			return '/';
		}
		
		return (string) $pathInfo;
	}
}