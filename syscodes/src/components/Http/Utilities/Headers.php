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

namespace Syscodes\Components\Http\Utilities;

use DateTime;
use Countable;
use Traversable;
use ArrayIterator;
use RuntimeException;
use DateTimeInterface;
use IteratorAggregate;

/**
 * Headers class is a container for HTTP headers.
 */
class Headers implements IteratorAggregate, Countable
{
	protected const STRING_UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	protected const STRING_LOWER = '-abcdefghijklmnopqrstuvwxyz';

	/**
	 * An array of HTTP headers.
	 * 
	 * @var array $herders
	 */
	protected $headers = [];
	
	/**
	 * Specifies the directives for the caching mechanisms in both
	 * the requests and the responses.
	 * 
	 * @var array $cacheControl
	 */
	protected $cacheControl = [];
	
	/**
	 * Constructor. The Headers class instance.
	 * 
	 * @param  array  $headers
	 * 
	 * @return void
	 */
	public function __construct(array $headers = [])
	{
		foreach ($headers as $key => $values) {
			$this->set($key, $values);
		}
	}
	
	/**
	 * Returns all the headers.
	 * 
	 * @param  string|null  $key  The name of the headers
	 * 
	 * @return array
	 */
	public function all(string $key = null): array
	{
		if (null !== $key) {
			return $this->headers[strtr($key, self::STRING_UPPER, self::STRING_LOWER)] ?? [];
		}

		return $this->headers;
	}
	
	/**
	 * Returns the parameter keys.
	 * 
	 * @return array An array of parameter keys
	 */
	public function keys(): array
	{
		return array_keys($this->all());
	}
	
	/**
	 * Replaces the current HTTP headers by a new set.
	 * 
	 * @param  array  $headers
	 * 
	 * @return void
	 */
	public function replace(array $headers = []): void
	{
		$this->headers = [];
		$this->add($headers);
	}
	
	/**
	 * Adds multiple header to the queue.
	 * 
	 * @param  array  $headers  The header name
	 * 
	 * @return mixed
	 */
	public function add(array $headers = [])
	{
		foreach ($headers as $key => $values) {
			$this->set($key, $values);
		}
	}
	
	/**
	 * Gets a header value by name.
	 *
	 * @param  string  $key  The header name, or null for all headers
	 * @param  string|null  $default  The default value
	 *
	 * @return mixed
	 */
	public function get(string $key, string $default = null): ?string
	{
		$headers = $this->all($key);
		
		if ( ! $headers) {
			return $default;
		}
		
		if (null === $headers[0]) {
			return null;
		}
		
		return (string) $headers[0];
	}

	/**
	 * Sets a header by name.
	 * 
	 * @param  string  $key  The header name
	 * @param  string|string[]|null  $values  The value or an array of values
	 * @param  bool  $replace  If you want to replace the value exists by the header, 
	 * 					       it is not overwritten / overwritten when it is false
	 *
	 * @return void
	 */
	public function set(string $key, $values, bool $replace = true): void
	{
		$key = strtr($key, self::STRING_UPPER, self::STRING_LOWER);

		if (is_array($values)) {
			$values = array_values($values);

			if (true === $replace || ! isset($this->headers[$key])) {
				$this->headers[$key] = $values;
			} else {
				$this->headers[$key] = array_merge($this->headers[$key], $values);
			}
		} else {
			if (true === $replace || ! isset($this->headers[$key])) {
				$this->headers[$key] = [$values];
			} else {
				$this->headers[$key][] = $values;
			}
		}
		
		if ('cache-control' === $key) {
			$this->cacheControl = $this->parseCacheControl(implode(', ', $this->headers[$key]));
		}
	}

	/**
	 * Returns true if the HTTP header is defined.
	 * 
	 * @param  string  $key  The HTTP header
	 * 
	 * @return bool  true if the parameter exists, false otherwise
	 */
	public function has(string $key): bool
	{
		return array_key_exists(strtr($key, self::STRING_UPPER, self::STRING_LOWER), $this->all());
	}

	/**
	 * Removes a header.
	 * 
	 * @param  string  $name  The header name
	 * 
	 * @return mixed
	 */
	public function remove(string $key)
	{
		$key = strtr($key, self::STRING_UPPER, self::STRING_LOWER);

		unset($this->headers[$key]);

		if ('cache-control' === $key) {
			$this->cacheControl = [];
		}
	}
	
	/**
	 * Returns the HTTP header value converted to a date.
	 * 
	 * @param  string  $key
	 * @param  DateTime|null  $default
	 * 
	 * @throws \RuntimeException When the HTTP header is not parseable
	 */
	public function getDate(string $key, DateTime $default = null): ?DateTimeInterface
	{
		if (null === $value = $this->get($key)) {
			return $default;
		}
		
		if (false === $date = DateTime::createFromFormat(DATE_RFC2822, $value)) {
			throw new RuntimeException(sprintf('The "%s" HTTP header is not parseable (%s).', $key, $value));
		}
		
		return $date;
	}
	
	/**
	 * Returns an iterator for headers.
	 * 
	 * @return \ArrayIterator An \ArrayIterator instance
	 */
	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->headers);
	}
	
	/**
	 * Returns the number of headers.
	 * 
	 * @return int The number of headers
	 */
	public function count(): int
	{
		return count($this->headers);
	}
	
	/**
	 * Parses a Cache-Control HTTP header.
	 * 
	 * @param string $header The value of the Cache-Control HTTP header
	 * 
	 * @return array An array representing the attribute values
	 */
	protected function parseCacheControl($header): array
	{
		$cacheControl = [];
		
		preg_match_all('~([a-zA-Z][a-zA-Z_-]*)\s*(?:=(?:"([^"]*)"|([^ \t",;]*)))?~', $header, $matches, PREG_SET_ORDER);
		
		foreach ($matches as $match) {
			$cacheControl[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
		}
		
		return $cacheControl;
	}
	
	/**
	 * Magic method.
	 * 
	 * Returns the headers as a string.
	 * 
	 * @return string The headers
	 */
	public function __toString(): string
	{
		if ( ! $headers = $this->all()) {
			return '';
		}
		
		ksort($headers);
		
		$max     = max(array_map('strlen', array_keys($headers))) + 1;
		$content = '';
		
		foreach ($headers as $name => $values) {
			$name = ucwords($name, '-');
			
			foreach ($values as $value) {
				$content .= sprintf("%-{$max}s %s\r\n", $name.':', $value);
			}
		}

		return $content;
	}
}