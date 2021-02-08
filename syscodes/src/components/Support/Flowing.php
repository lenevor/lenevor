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

namespace Syscodes\Support;

use ArrayAccess;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Checks if exist an attribute in flowing instance for collections of data.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Flowing implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    /**
     * All of the attributes set on the container.
     * 
     * @var array $attributes
     */
    protected $attributes = [];

    /**
     * Constructor. Create a new Flowing class instance.
     * 
     * @param  array|object  $attributes
     * 
     * @return void
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $values) {
            $this->attributes[$key] = $values;
        }
    }

    /**
     * Get an attribute from flowing instance.
     * 
     * @param  string  $key
     * @param  mixed  $default  (null by default)
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return value($default);
    }

    /**
     * Get the attributes from flowing instance.
     * 
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convert flowing instance to an array.
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert flowing instance to JSON.
     * 
     * @param  int  $options  (0 by default)
     * 
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    
    /*
    |-----------------------------------------------------------------
    | ArrayAccess Methods
    |-----------------------------------------------------------------
    */

    /**
     * Determine if a given offset exists.
     * 
     * @param  string  $offset
     * 
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Get the value at a given offset.
     * 
     * @param  string  $offset
     * 
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Set the value at a given offset.
     * 
     * @param  string  $offset
     * @param  mixed  $value
     * 
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Unset the value at a given offset.
     * 
     * @param  string  $offset
     * 
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    /**
	 * Magic method. Searches for the given variable and returns its value.
	 *
	 * @param  string  $key  Variable name
	 *
	 * @return mixed
	 */
	public function __get($key) 
	{
		return $this->get($key);
	}

	/**
	 * Magic method. Calls [$this->set] with the same parameters.
	 *
	 * @param  string  $key    Variable name
	 * @param  mixed   $value  Value
	 *
	 * @return void
	 */
	public function __set($key, $value) 
	{
		$this->offsetSet($key, $value);
	}

	/**
	 * Magic method. Determines if a variable is set.
	 *
	 * @param  string  $key  variable name
	 *
	 * @return boolean
	 */
	public function __isset($key) 
	{
		return $this->offsetExists($key);
	}

	/**
	 * Magic method. Unsets a given variable.
	 *
	 * @param  string  $key  Variable name
	 *
	 * @return void
	 */
	public function __unset($key) 
	{
		$this->offsetUnset($$key);
	}

    /**
     * Handle dynamic calls to the container to set attributes.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }
}