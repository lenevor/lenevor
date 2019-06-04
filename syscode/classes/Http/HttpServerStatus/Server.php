<?php

namespace Syscode\Http;

use Syscode\Support\Arr;

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
 * @copyright   Copyright (c) 2019 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */
class Server
{
	/**
	 * Array data from the Server global.
	 *
	 * @var array $data
	 */
	protected $data = [];

	/**
	 * Server Object Constructor.
	 *
	 * @param  array  $array
	 *
	 * @return array
	 */
	public function __construct($array)
	{
		$array = ! $array ?: $_SERVER;

		$this->data = $array;
	}

	/**
	 * Get a server array item.
	 *
	 * @param  string       $key
	 * @param  string|null  $fallback 
	 *
	 * @return mixed
	 */
	public function get($key, $fallback = null)
	{
		if (Arr::exists($this->data, $key))
		{
			return $this->data[$key];
		}

		return $fallback;
	}

	/**
	 * Check if a server array item exists.
	 *
	 * @param  string  $key
	 *
	 * @return mixed
	 */
	public function has($key)
	{
		return Arr::exists($this->data, $key);
	}

	/**
	 * Set a server array item.
	 *
	 * @param  string  $key
	 * @param  string  $value 
	 *
	 * @return mixed
	 */
	public function set($key, $value)
	{
		$this->data = [$key => $value];
	}

	/**
	 * Remove a server array item.
	 *
	 * @param  string  $key 
	 *
	 * @return void
	 */
	public function remove($key)
	{
		if ($this->has($key))
		{
			unset($this->data[$key]);
		}
	}
}