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

use Syscodes\Components\Support\Str;

/**
 * Returns the HTTP requests is filtered and detected in the routes set by the user.
 */
trait HttpResources
{
	/**
	 * Filters a value from the start of a string in this case the passed URI string.
	 *
	 * @return string
	 */
	protected function parseRequestUri(): string
	{
		$requestUri = '';
		
		if ('1' == $this->server->get('IIS_WasUrlRewritten') && '' != $this->server->get('UNENCODED_URL')) {
			// IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
			$requestUri = $this->server->get('UNENCODED_URL');
			$this->server->remove('UNENCODED_URL');
			$this->server->remove('IIS_WasUrlRewritten');
		} elseif ($this->server->has('REQUEST_URI')) {
			$requestUri = $this->server->get('REQUEST_URI');
			
			if ('' !== $requestUri && '/' === $requestUri[0]) {
				// To only use path and query remove the fragment.
				if (false !== $pos = strpos($requestUri, '#')) {
					$requestUri = substr($requestUri, 0, $pos);
				}
			} else {
				// HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
				// only use URL path.
				$uriComponents = parse_url($requestUri);
				
				if (isset($uriComponents['path'])) {
					$requestUri = $uriComponents['path'];
				}
				
				if (isset($uriComponents['query'])) {
					$requestUri .= '?'.$uriComponents['query'];
				}
			}
		} elseif ($this->server->has('ORIG_PATH_INFO')) {
			// IIS 5.0, PHP as CGI
			$requestUri = $this->server->get('ORIG_PATH_INFO');
			
			if ('' != $this->server->get('QUERY_STRING')) {
				$requestUri .= '?'.$this->server->get('QUERY_STRING');
			}
			
			$this->server->remove('ORIG_PATH_INFO');
		}
		
		// normalize the request URI to ease creating sub-requests from this request
		$this->server->set('REQUEST_URI', $requestUri);
		
		return $this->filterDecode($requestUri);
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
		$requestUri = $this->getRequestUri();
		
		if ('' !== $requestUri && '/' !== $requestUri[0]) {
			$requestUri = '/'.$requestUri;
		}
		
		if ($baseUrl && null !== $uri = $this->getUrlencoded($requestUri, $baseUrl)) {
			// Full $baseUrl matches
			return $this->filterDecode($uri);
		}
		
		if ($baseUrl && null !== $uri = $this->getUrlencoded($requestUri, rtrim(dirname($baseUrl), '/'.DIRECTORY_SEPARATOR))) {
			// Directory portion of $baseUrl matches
			return $this->filterDecode($uri);
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
	 * Returns the prefix as encoded in the string when the string starts with
	 * the given prefix, null otherwise.
	 *
	 * return string|null
	 */
	private function getUrlencoded(string $string, string $prefix): ?string
	{
		if ( ! Str::startsWith(rawurldecode($string), $prefix)) {
			return null;
		}
		
		$length = strlen($prefix);
		
		if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $length), $string, $match)) {
			return $match[0];
		}
		
		return null;
	}


	/**
	 * Parse the path info.
	 * 
	 * @return string
	 */
	public function parsePathInfo(): string
	{
		if (null === ($requestUri = $this->getRequestUri())) {
			return '/';
		}
		
		// Remove the query string from REQUEST_URI
		if (false !== $pos = strpos($requestUri, '?')) {
			$requestUri = substr($requestUri, 0, $pos);
		}
		
		if ('' !== $requestUri && '/' !== $requestUri[0]) {
			$requestUri = '/'.$requestUri;
		}
		
		if (null === ($baseUrl = $this->getBaseUrl())) {
			return $requestUri;
		}
		
		$pathInfo = substr($requestUri, \strlen($baseUrl));
		
		if (false === $pathInfo || '' === $pathInfo) {
			// If substr() returns false then PATH_INFO is set to an empty string
			return '/';
		}
		
		return $this->filterDecode($pathInfo);
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
		$uri = mb_strtolower(trim($uri), 'UTF-8');
		
		// Return argument if not empty or return a single slash
		return trim($uri);
	}
}