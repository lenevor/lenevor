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

namespace Syscodes\Components\Http\Response;

use InvalidArgumentException;
use Syscodes\Components\Http\Cookie;
use Syscodes\Components\Http\Contributors\Headers;

/**
 * ResponseHeaders is a container for Response HTTP headers.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ResponseHeaders extends Headers
{
	const COOKIE_FLAT = 'flat';
	const COOKIE_ARRAY = 'array';
	
	/**
	 * The list of cookies.
	 * 
	 * @var array $cookies
	 */
	protected $cookies = [];

    /**
     * The header names.
	 * 
	 * @var array $headerNames 
     */
	protected $headerNames = [];

	/**
	 * Constructor. Create a new ResponseHeaders class instance.
	 * 
	 * @param  array  $headers
	 * 
	 * @return void 
	 */
	public function __construct(array $headers = [])
	{
		parent::__construct($headers);
		
		if ( ! isset($this->headers['cache-control'])) {
			$this->set('Cache-Control', '');
		}
	}

    /**
	 * Returns the headers, with original capitalizations.
	 * 
	 * @return array An array of headers
	 */
	public function allPreserveCase(): array
	{
		$headers = [];
		
		foreach ($this->all() as $name => $value) {
			$headers[$this->headerNames[$name] ?? $name] = $value;
		}
		
		return $headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace(array $headers = []): void
	{
		$this->headerNames = [];

		parent::replace($headers);

		if ( ! isset($this->headers['cache-control'])) {
			$this->set('Cache-Control', '');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function set(string $key, $values, bool $replace = true): self
	{
		$unique = strtr($key, self::STRING_UPPER, self::STRING_LOWER); 
		
		if ('set-cookie' === $unique) {
			if ($replace) {
				$this->cookies = [];
			}
			
			foreach ((array) $values as $cookie) {
				$this->setCookie($cookie);
			}
			
			$this->headerNames[$unique] = $key;
			
			return $this;
		}
		
		$this->headerNames[$unique] = $key;
		
		return parent::set($key, $values, $replace);
	}
	
	/**
	 * Gets an array with all cookies.
	 * 
	 * @param  string  $format
	 * 
	 * @return Cookie[]
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function getCookies(string $format = self::COOKIE_FLAT): array
	{
		if ( ! in_array($format, [self::COOKIE_FLAT, self::COOKIE_ARRAY])) {
			throw new InvalidArgumentException(
				sprintf('Format "%s" invalid (%s)', $format, implode(', ', [self::COOKIE_FLAT, self::COOKIE_ARRAY])
			));
		}
		
		if (self::COOKIE_ARRAY === $format) {
			return $this->cookies;
		}
		
		$stringCookies = [];

		foreach ($this->cookies as $path) {
			foreach ($path as $cookies) {
				foreach ($cookies as $cookie) {
					$stringCookies[] = $cookie;
				}
			}
		}
		
		return $stringCookies;
    }


	/**
	 * Sets the cookie.
	 * 
	 * @param  \Syscodes\Components\Cookie\CookieManager
	 */
	public function setCookie(Cookie $cookie): void
	{
		$this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
		$this->headerNames['set-cookie'] = 'Set-Cookie';
	}

	/**
	 * Initialize the date.
	 * 
	 * @return void
	 */
	private function initDate(): void
	{
		//
	}
}
