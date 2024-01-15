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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Loaders;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Http\Exceptions\BadRequestException;

/**
 * Parameters is a container for key/value pairs.
 */
class Parameters implements IteratorAggregate, Countable
{
	/**
	 * Array parameters from the Server global.
	 *
	 * @var array $parameters
	 */
	protected $parameters;

	/**
	 * Parameter Object Constructor.
	 *
	 * @param  array  $parameters
	 *
	 * @return array
	 */
	public function __construct(array $parameters = [])
	{
		$this->parameters = $parameters;
	}

	/**
	 * Returns the parameters.
	 * 
	 * @param  string|null  $key
	 * 
	 * @return array
	 */
	public function all(string $key = null): array
	{
		if (null === $key) {
			return $this->parameters;
		}

		if ( ! is_array($value = $this->parameters[$key] ?? [])) {
			throw new BadRequestException(
				sprintf('Unexpected value for parameter "%s", got "%s"', $key, get_debug_type($value))
			);
		}

		return $value;
	}

	/**
	 * Returns the parameter keys.
	 * 
	 * @return array
	 */
	public function keys(): array
	{
		return array_keys($this->parameters);
	}

	/**
	 * Replaces the current parameters.
	 * 
	 * @param  array  $parameters
	 * 
	 * @return void
	 */
	public function replace(array $parameters = []): void
	{
		$this->parameters = $parameters;
	}

	/**
	 * Adds parameters.
	 * 
	 * @param  array  $parameters
	 * 
	 * @return void
	 */
	public function add(array $parameters = []): void
	{
		$this->parameters = array_replace($this->parameters, $parameters);
	}

	/**
	 * Get a parameter array item.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  
	 *
	 * @return mixed
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->has($key) ? $this->parameters[$key] : $default;
	}

	/**
	 * Check if a parameter array item exists.
	 *
	 * @param  string  $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return Arr::exists($this->parameters, $key);
	}

	/**
	 * Set a parameter array item.
	 *
	 * @param  string  $key
	 * @param  string  $value 
	 *
	 * @return void
	 */
	public function set(string $key, $value): void
	{
		$this->parameters[$key] = $value;
	}

	/**
	 * Remove a parameter array item.
	 *
	 * @param  string  $key 
	 *
	 * @return void
	 */
	public function remove(string $key): void
	{
		unset($this->parameters[$key]);
	}

	/*
	|-----------------------------------------------------------------
	| IteratorAggregate Method
	|-----------------------------------------------------------------
	*/
	
	/**
	 * Retrieve an external iterator.
	 * 
	 * @return \ArrayIterator
	 */
	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->parameters);
	}
	
	/*
	|-----------------------------------------------------------------
	| Countable Method
	|-----------------------------------------------------------------
	*/
	
	/**
	 * Returns the number of parameters.
	 * 
	 * @return int The number of parameters
	 */
	public function count(): int
	{
		return count($this->parameters);
	}
}