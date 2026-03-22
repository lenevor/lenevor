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

namespace Syscodes\Components\Config;

use ArrayAccess;
use InvalidArgumentException;
use Syscodes\Components\Contracts\Config\Configure as ConfigureContract;
use Syscodes\Components\Support\Arr;

/**
 * Class Configure
 * 
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values.
 */
class Configure implements ArrayAccess, ConfigureContract
{
	/**
	 * Get the files.
	 * 
	 * @var array $files
	 */
	protected array $files = [];

	/**
	 * Currently registered routes.
	 * 
	 * @var array $vars
	 */
	protected array $vars = [];

	/**
	 * Constructor. Create a new Configure class instance.
	 * 
	 * @param  array  $files
	 * 
	 * @return void
	 */
	public function __construct(array $files = [])
	{
		$this->files = $files;
	}

	/**
	 * Determine if the given configuration value exists.
	 * 
	 * @param  string  $key
	 * 
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return Arr::has($this->vars, $key);
	}

	/**
	 * Returns a (dot notated) config setting.
	 *
	 * @param  string  $key  The dot-notated key or array of keys
	 * @param  mixed  $default  The default value
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$keys = explode('.', $key);

		if ( ! array_key_exists($file = head($keys), $this->vars)) {
			foreach ($this->files as $paths) {
				if (is_readable($path = dirname($paths).DIRECTORY_SEPARATOR.$file.'.php')) {
					$this->vars[$file] = require $path;
				}				
			}
		}
		
		return Arr::get($this->vars, $key, $default);
	}

	/**
	 * Sets a value in the config array.
	 *
	 * @param  array|string  $key  The dot-notated key or array of keys
	 * @param  mixed  $value  The default value
	 *
	 * @return void
	 */
	public function set($key, $value = null)
	{
		$keys = is_array($key) ? $key : [$key => $value];
		
		foreach ($keys as $key => $value) {
			Arr::set($this->vars, $key, $value);
		}
	}

	/**
	 * Deletes a (dot notated) config item.
	 * 
	 * @param  string  $key  A (dot notated) config key
	 * 
	 * @return void
	 */
	public function erase(string $key)
	{
		if (isset($this->vars[$key])) {
			unset($this->vars[$key]);
		}
		
		Arr::erase($this->vars, $key);
	}
	
	/**
	 * Get the specified string configuration value.
	 * 
	 * @param  string  $key
	 * 
	 * @param  \Closure|string|null  $default
	 * 
	 * @return string
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function string(string $key, $default = null): string
	{
		$value = $this->get($key, $default);
		
		if ( ! is_string($value)) {
			throw new InvalidArgumentException(
				sprintf('Configuration value for key [%s] must be a string, %s given.', $key, gettype($value))
			);
		}
		
		return $value;
	}
	
	/**
	 * Get the specified float configuration value.
	 * 
	 * @param  string  $key
	 * @param  \Closure|float|null  $default
	 * 
	 * @return float
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function float(string $key, $default = null): float
	{
		$value = $this->get($key, $default);
		
		if ( ! is_float($value)) {
			throw new InvalidArgumentException(
				sprintf('Configuration value for key [%s] must be a float, %s given.', $key, gettype($value))
			);
		}
		
		return $value;
	}
	
	/**
	 * Get the specified boolean configuration value.
	 * 
	 * @param  string  $key
	 * @param  \Closure|bool|null  $default
	 * 
	 * @return bool
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function boolean(string $key, $default = null): bool
	{
		$value = $this->get($key, $default);
		
		if ( ! is_bool($value)) {
			throw new InvalidArgumentException(
				sprintf('Configuration value for key [%s] must be a boolean, %s given.', $key, gettype($value))
			);
		}
		
		return $value;
	}

	/**
	 * Get all of the configuration items for the application.
	 * 
	 * @return array
	 */
	public function all(): array
	{
		return $this->vars;
	}
	
	/*
	|-----------------------------------------------------------------
	| ArrayAccess Methods
	|-----------------------------------------------------------------
	*/ 
	
	/**
	 * Determine if the given configuration option exists.
	 * 
	 * @param  mixed  $key
	 * 
	 * @return bool
	 */
	public function offsetExists(mixed $key): bool
	{
		return $this->has($key);
	}
	
	/**
	 * Get a configuration option.
	 * 
	 * @param  mixed  $key
	 * 
	 * @return mixed
	 */
	public function offsetGet(mixed $key): mixed
	{
		return $this->get($key);
	}
	
	/**
	 * Set a configuration option.
	 * 
	 * @param  mixed  $key
	 * @param  mixed  $value
	 * 
	 * @return void
	 */
	public function offsetSet(mixed $key, mixed $value): void
	{
		$this->set($key, $value);
	}
	
	/**
	 * Unset a configuration option.
	 * 
	 * @param  mixed  $key
	 * 
	 * @return void
	 */
	public function offsetUnset(mixed $key): void
	{
		$this->set($key, null);
	}
}