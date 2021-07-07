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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Http\Contributors;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Syscodes\Collections\Arr;
use Syscodes\Http\Exceptions\BadRequestException;

/**
 * Parameters is a container for key/value pairs.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Parameters implements IteratorAggregate, Countable
{
	/**
	 * Array parameters from the Server global.
	 *
	 * @var array $parameters
	 */
	protected $parameters = [];

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
	public function all()
	{
		$key = func_num_args() > 0 ? func_get_arg(0) : null;

		if ( ! is_array($value = $this->parameters[$key] ?? [])) {
			throw new BadRequestException(sprinf("Unexpected value for parameter %s, got %s", $key, get_debug_type($value)));
		}

		return (null === $key) ? $this->parameters : $value;
	}

	/**
	 * Returns the parameter keys.
	 * 
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->parameters);
	}

	/**
	 * Replaces the current parameters.
	 * 
	 * @param  array  $parameters
	 * 
	 * @return array
	 */
	public function replace(array $parameters = [])
	{
		$this->parameters = $parameters;
	}

	/**
	 * Adds parameters.
	 * 
	 * @param  array  $parameters
	 * 
	 * @return array
	 */
	public function add(array $parameters = [])
	{
		$this->parameters = array_replace($this->parameters, $parameters);
	}

	/**
	 * Get a parameter array item.
	 *
	 * @param  string  $key
	 * @param  string|null  $default  
	 *
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->has($key) ? $this->parameters[$key] : $default;
	}

	/**
	 * Check if a parameter array item exists.
	 *
	 * @param  string  $key
	 *
	 * @return mixed
	 */
	public function has(string $key)
	{
		return Arr::exists($this->parameters, $key);
	}

	/**
	 * Set a parameter array item.
	 *
	 * @param  string  $key
	 * @param  string  $value 
	 *
	 * @return mixed
	 */
	public function set(string $key, $value)
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
	public function remove(string $key)
	{
		if ($this->has($key)) {
			unset($this->parameters[$key]);
		}
	}

	/*
	|-----------------------------------------------------------------
	| IteratorAggregate Method
	|-----------------------------------------------------------------
	*/
	
	/**
	 * Retrieve an external iterator.
	 * 
	 * @see    \IteratorAggregate::getIterator
	 * 
	 * @return new \ArrayIterator
	 */
	public function getIterator()
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
	public function count()
	{
		return count($this->parameters);
	}
}