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

namespace Syscodes\Components\Http\Loaders;

use Syscodes\Components\Core\Http\Exceptions\BadRequestHttpException;

/**
 * Inputs is a container for user input values such as 
 * $_GET, $_POST, $_REQUEST, and $_COOKIE.
 */
final class Inputs extends Parameters
{
	/**
	 * Replaces the current parameters.
	 * 
	 * @param  array  $parameters
	 * 
	 * @return void
	 */
	public function replace(array $inputs = []): void
	{
		$this->parameters = [];
		$this->add($inputs);
	}
	
	/**
	 * Adds parameters.
	 * 
	 * @param  array  $parameters
	 * 
	 * @return void
	 */
	public function add(array $inputs = []): void
	{
		foreach ($inputs as $input => $file) {
			$this->set($input, $file);
		}
	}
	
	/**
	 * Gets a string input value by name.
	 * 
	 * @param  string  $key
	 * @param  mixed  $default  
	 * 
	 * @return string|int|float|bool|null
	 */
	public function get(string $key, mixed $default = null): string|int|float|bool|null
	{
		if (null !== $default && ! is_scalar($default) && ! method_exists($default, '__toString')) {
			throw new BadRequestHttpException(sprintf('Passing a non-string value as 2nd argument to "%s()" is deprecated, pass a string or null instead', __METHOD__));
		}
		
		$value = parent::get($key, $this);
		
		if (null !== $value && $this !== $value && ! is_scalar($value) && ! method_exists($value, '__toString')) {
			throw new BadRequestHttpException(sprintf('Retrieving a non-string value from "%s()" is deprecated, and will throw a exception in Syscodes, use "%s::all($key)" instead', __METHOD__, __CLASS__));
		}
		
		return $this === $value ? $default : $value;
	}
	
	/**
	 * Sets an input by name.
	 * 
	 * @param  string  $key
	 * @param  mixed  $value
	 * 
	 * @return void
	 */
	public function set(string $key, mixed $value): void
	{
		if (null !== $value && ! is_scalar($value) && ! is_array($value) && ! method_exists($value, '__toString')) {
			throw new BadRequestHttpException(sprintf('Passing "%s" as a 2nd Argument to "%s()" is deprecated, pass a string, array, or null instead', get_debug_type($value), __METHOD__));
		}
		
		$this->parameters[$key] = $value;
	}
}