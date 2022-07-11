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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Http\Exceptions\HttpURIException;

/**
 * Abstraction for a uniform resource identifier (URI).
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class URI
{
	/**
	 * Returns default schemes/ports.
	 * 
	 * @var array $defaultPorts
	 */
	protected $defaultPorts = [
		'http'  => 80,
		'https' => 443,
		'ftp'   => 21,
		'sftp'  => 22
	];

	/**
	 * The name of any fragment.
	 * 
	 * @var string $fragment
	 */
	protected $fragment = '';

	/**
	 * The URI Host.
	 * 
	 * @var string $host
	 */
	protected $host;
	
	/**
	 * The URI User Password.
	 * 
	 * @var string $password
	 */
	protected $password;

	/**
	 * The URI path.
	 * 
	 * @var string $path
	 */
	protected $path;

	/**
	 * The URI Port.
	 * 
	 * @var int $port
	 */
	protected $port;

	/**
	 * The query string.
	 * 
	 * @var string $query
	 */
	protected $query;

	/**
	 * The URI Scheme.
	 * 
	 * @var string $scheme
	 */
	protected $scheme = 'http';

	/**
	 * The URI segments.
	 *
	 * @var array $segments
	 */
	protected $segments = [];

	/**
	 * Whether passwords should be shown in userInfo/authority calls.
	 * 
	 * @var boolean $showPassword
	 */
	protected $showPassword = false;
	
	/**
	 * The URI User Info.
	 * 
	 * @var string $user
	 */
	protected $user;

	/**
	 * Constructor. The URI class instance.
	 * 
	 * @param  string|null  $uri  
	 * 
	 * @return void
	 * 
	 * @throws \Syscodes\Components\Http\Exceptions\HttpURIException
	 */
	public function __construct(string $uri = null)
	{
		if ( ! is_null($uri)) {
			$this->setUri($uri);
		}
	}

	/**
	 * Sets and overwrites any current URI information.
	 * 
	 * @param  string|null  $uri  
	 * 
	 * @return mixed
	 * 
	 * @throws \Syscodes\Components\Http\Exceptions\HttpURIException
	 */
	public function setUri(string $uri = null)
	{
		if ( ! is_null($uri)) {
			$parts = parse_url($uri);

			if ($parts === false) {
				throw HttpURIException::UnableToParseURI($uri);
			}

			$this->applyParts($parts);
		}

		return $this;
	}

	/**
	 * Returns the full URI string.
	 *
	 * @return string  The URI string
	 */
	public function get(): string
	{
		return '/'.ltrim($this->path, '/');
	}

	/**
	 * Sets of URI string.
	 * 
	 * @param  string  $uri
	 * 
	 * @return self
	 */
	public function set($uri): self
	{
		$this->path = $uri;

		return $this;
	}

	/**
	 * Retrieve the path component of the URI. The path can either be empty or absolute 
	 * (starting with a slash) or rootless (not starting with a slash).
	 * 
	 * @return string
	 */
	public function getPath(): string
	{
		return (is_null($this->path) ? '' : $this->path);
	}

	/**
	 * Sets the path portion of the URI.
	 * 
	 * @param  string  $path
	 *
	 * @return self
	 */
	public function setPath(string $path): self
	{
		$this->path = $this->filterPath($path);

		$tempPath = trim($this->path, '/');

		$this->filterSegments($tempPath);

		return $this;
	} 

	/**
	 * Encodes any dangerous characters.
	 * 
	 * @param  string|null  $path
	 * 
	 * @return string
	 */
	protected function filterPath(string $path = null): string
	{
		$path = urldecode($path);

		return $path;
	}

	/**
	 * Filter the segments of path.
	 * 
	 * @param  string  $uri
	 * 
	 * @return string[]
	 */
	protected function filterSegments($uri)
	{
		$this->segments = ($uri === '') ? [] : explode('/', $uri);
	}

	/**
	 * Get the specified URI segment, return default if it doesn't exist.
	 * Segment index is 1 based, not 0 based.
	 *
	 * @param  int  $index  The 1-based segment index
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public function getSegment(int $index, $default = null)
	{
		return Arr::get($this->getSegments(), $index - 1, $default);
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
	public function getTotalSegments(): int
	{
		return count($this->getSegments());
	}

	/**
	 * Retrieve the scheme component of the URI.
	 * 
	 * @return string
	 */
	public function getScheme(): string
	{
		return $this->scheme;
	}

	/**
	 * Sets the scheme for this URI.
	 * 
	 * @param  string  $str
	 * 
	 * @return string
	 */
	public function setScheme(string $str): string
	{
		$str = preg_replace('~:(//)?$~', '', strtolower($str));

		$this->scheme = $str;

		return $this->scheme;
	}

	/**
	 * Retrieve the user component of the URI.
	 * 
	 * @return string|null
	 */
	public function getUserInfo()
	{
		$user = $this->user;

		if ($this->showPassword === true && ! empty($this->password)) {
			$user .= ":$this->password";
		}

		return $user;
	}

	/**
	 * Sets the userInfo/Authority portion of the URI.
	 * 
	 * @param  string  $user
	 * @param  string  $pass
	 * 
	 * @return self
	 */
	public function setUserInfo(string $user, string $pass): self
	{
		$this->user     = trim($user);
		$this->password = trim($pass);

		return $this;
	}

	/**
	 * Temporarily sets the URI to show a password in userInfo.
	 * 
	 * @param  boolean  $option  
	 * 
	 * @return string
	 */
	public function showPassword(bool $option = true): string
	{
		$this->password = $option;

		return $this->password;
	}

	/**
	 * Retrieve the authority component of the URI.
	 * 
	 * @param  boolean  $ignore  
	 * 
	 * @return string
	 */
	public function getAuthority(bool $ignore = false): string
	{
		if (empty($this->host)) {
			return '';
		}

		$authority = $this->host;

		if ( ! empty($this->getUserInfo())) {
			$authority = $this->getUserInfo().'@'.$authority;
		}

		if ( ! empty($this->port) && ! $ignore) {
			if ($this->port !== $this->defaultPorts[$this->scheme]) {
				$authority .= ":$this->port";
			}
		}

		$this->showPassword = false;

		return $authority;
	}

	/**
	 * Parses the given string an saves the appropriate authority pieces.
	 * 
	 * @param  string  $str
	 * 
	 * @return self
	 */
	public function setAuthority(string $str): self
	{
		$parts = parse_url($str);

		if (empty($parts['host']) && ! empty($parts['path'])) {
			$parts['host'] = $parts['path'];
			unset($parts['path']);
		}

		$this->applyParts($parts);

		return $this;
	}

	/**
	 * Retrieve the host component of the URI.
	 * 
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * Sets the host name to use.
	 * 
	 * @param  string  $str
	 * 
	 * @return string
	 */
	public function setHost(string $str): string
	{
		$this->host = trim($str);

		return $this->host;
	}

	/**
	 * Retrieve the port component of the URI.
	 * 
	 * @return int|null
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Sets the port portion of the URI.
	 * 
	 * @param  int|null  $port  
	 * 
	 * @return string
	 */
	public function setPort(int $port = null): string
	{
		if (is_null($port)) {
			return $this;
		}

		if ($port <= 0 || $port > 65355) {
			throw HttpURIException::invalidPort($port);
		}

		$this->port = $port;

		return $this->port;
	}

	/**
	 * Retrieve a URI fragment.
	 * 
	 * @return string
	 */
	public function getFragment(): string
	{
		return is_null($this->fragment) ? '' : $this->fragment;
	}

	/**
	 * Sets the fragment portion of the URI.
	 * 
	 * @param  string  $str
	 * 
	 * @return string
	 */
	public function setFragment(string $str): string
	{
		$this->fragment = trim($str, '# ');

		return $this->fragment;
	}

	/**
	 * Saves our parts from a parse_url call.
	 * 
	 * @param  array  $parts
	 * 
	 * @return mixed
	 */
	public function applyParts(array $paths)
	{
		if (isset($parts['scheme'])) {
			$this->SetScheme(rtrim($parts['scheme'], ':/'));
		} else {
			$this->setScheme('http');
		}

		if ( ! empty($parts['host'])) {
			$this->host = $parts['host'];
		}

		if (isset($parts['port'])) {
			if ( ! is_null($parts['port'])) {
				$this->port = $parts['port'];
			}
		}

		if ( ! empty($parts['user'])) {
			$this->user = $parts['user'];
		}

		if ( ! empty($parts['pass'])) {
			$this->password = $parts['pass'];
		}

		if ( ! empty($parts['path'])) {
			$this->path = $this->filterPath($parts['path']);
		}

		if ( ! empty($parts['fragment'])) {
			$this->fragment = $parts['fragment'];
		}

		if ( ! empty($parts['path'])) {
			$this->segments = explode('/', $parts['path'], '/');
		}
	}

	/**
	 * Magic method.
	 * 
	 * Returns the URI string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->getPath();
	}
}