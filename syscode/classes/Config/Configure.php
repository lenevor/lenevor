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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Config;

use Syscode\Support\Arr;
use Syscode\Contracts\Config\Configure as ConfigureContract;

/**
 * Class Configure
 * 
 * Not intended to be used on its own, this class will attempt to
 * automatically populate the child class' properties with values.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Configure implements ConfigureContract
{
	/**
	 * Currently registered routes.
	 * 
	 * @var array $vars
	 */
	public static $vars = [];

	/**
	 * Returns a (dot notated) config setting.
	 *
	 * @param  string  $key      The dot-notated key or array of keys
	 * @param  mixed   $default  The default value
	 *
	 * @return mixed
	 *
	 * @uses   \Syscode\Support\Arr
	 */
	public static function get(string $key, $default = null)
	{
		$keys = explode('.', $key);

		if ( ! array_key_exists($file = current($keys), static::$vars))
		{
			foreach ([CON_PATH, SYS_PATH.'config/'] as $paths)
			{
				if (is_readable($path = $paths.$file.'.php'))
				{
					static::$vars[$file] = require $path;
				}				
			}
		} 

		return Arr::get(static::$vars, $key, $default);
	}

	/**
	 * Sets a value in the config array.
	 *
	 * @param  string  $key    The dot-notated key or array of keys
	 * @param  mixed   $value  The default value
	 *
	 * @return mixed
	 *
	 * @uses   \Syscode\Support\Arr
	 */
	public function set(string $key, $value)
	{
		strpos($key, '.') === false OR static::$vars[$key] = $value;
		
		Arr::set(static::$vars, $key, $value);
	}

	/**
	 * Deletes a (dot notated) config item.
	 *
	 * @param  string  $key  A (dot notated) config key
	 *
	 * @return array|bool
	 *
	 * @uses   \Syscode\Support\Arr
	 */
	public function erase(string $key)
	{
		if (isset(static::$vars[$key]))
		{
			unset(static::$vars[$key]);
		}
		
		Arr::erase(static::$vars, $key);
	}	

	/**
	 * Returns a value from the config array using the method call 
	 * as the file reference.
	 *
	 * @example Configure::app('baseUrl');
	 *
	 * @param  string  $name   The variable name     
	 * @param  array   $value  Value
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $value)
	{
		$key      = $name;
		$fallback = null;

		if (count($value))
		{
			$key      .= '.'.array_shift($value);
			$fallback  = array_shift($value);
		}

		return static::get($key, $fallback);
	}
}