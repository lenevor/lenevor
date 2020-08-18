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
 * @since       0.7.2
 */

namespace Syscodes\Http\Contributors;

use Syscodes\Core\Http\Exceptions\BadRequestHttpException;

/**
 * Inputs is a container for user input values such as 
 * $_GET, $_POST, $_REQUEST, and $_COOKIE.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
final class Inputs extends Parameters
{
    /**
	 * {@inheritdoc}
	 */
	public function all(string $key = null)
	{
		return parent::all($key);
    }
    
    /**
	 * {@inheritdoc}
	 */
	public function replace(array $inputs = [])
	{
		$this->parameters = [];
        $this->add($inputs);
	}

	/**
	 * Adds input values.
     * 
     * @param  array  $inputs
     * 
     * @return mixed
	 */
	public function add(array $inputs = [])
	{
        foreach ($inputs as $key => $file)
        {
            $this->set($key, $file);
        }
    }
    
    /**
	 * Gets a string input value by name.
	 *
	 * @param  string  $key
	 * @param  string|null  $default  (null by default)
	 *
	 * @return string|null
	 */
	public function get($key, $default = null)
	{
        if (null !== $default && ! is_scalar($default) && ! (is_object($default)))
        {
            throw new BadRequestHttpException(sprintf('Passing a non-string value as 2nd argument to "%s()" is deprecated, pass a string or null instead', __METHOD__));
        }

        $value = parent::get($key, $this);

        if (null !== $value && $this !== $value && ! is_scalar($value) && ! (is_object($value)))
        {
            throw new BadRequestHttpException(sprintf('Retrieving a non-string value from "%s()" is deprecated, and will throw a exception in Syscodes, use "%s::all($key)" instead', __METHOD__, __CLASS__));
        }
        
        return $value === $this ? $default : $value;
    }
    
    /**
	 * Sets an input by name.
	 *
	 * @param  string  $key
	 * @param  string|array|null  $value 
	 *
	 * @return mixed
	 */
	public function set($key, $value)
	{
        if (null !== $value && ! is_scalar($value) && ! is_array($value))
        {
            throw new BadRequestHttpException(sprintf('Passing "%s" as a 2nd Argument to "%s()" is deprecated, pass a string, array, or null instead', get_debug_type($value), __METHOD__));
        }

		$this->parameters[$key] = $value;
	}
}