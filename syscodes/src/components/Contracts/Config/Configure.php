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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Config;

/**
 * Sets functions for the config item.
 */
interface Configure
{
	/**
	 * Determine if the given configuration value exists.
	 * 
	 * @param  string  $key
	 * 
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * Deletes a (dot notated) config item.
	 * 
	 * @param  string  $key  A (dot notated) config key
	 * 
	 * @return void
	 */	
	public function erase(string $key);

	/**
	 * Returns a (dot notated) config setting.
	 *
	 * @param  string  $key  The dot-notated key or array of keys
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public function get($key, $default = null);
	
	/**
	 * Sets a value in the config array.
	 *
	 * @param  string  $key  The dot-notated key or array of keys
	 * @param  mixed  $value  The default value
	 *
	 * @return void
	 */
	public function set($key, $value = null);

	/**
	 * Get all of the configuration items for the application.
	 * 
	 * @return array
	 */
	public function all(): array;
}