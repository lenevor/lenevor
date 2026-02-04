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

namespace Syscodes\Components\Support;

use ArrayAccess;
use JsonSerializable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Checks if exist an attribute in flowing instance for collections of data.
 */
class Flowing implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    use Macroable {
        __call as macroCall;
    }
    
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
        $this->fill($attributes);
    }
    
    /**
     * Create a new flowing instance.
     * 
     * @param  iterable  $attributes
     * 
     * @return static
     */
    public static function make($attributes = []): static
    {
        return new static($attributes);
    }

    /**
     * Get an attribute from flowing instance.
     * 
     * @param  string  $key
     * @param  mixed  $default  
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return data_get($this->attributes, $key, $default);
    }
    
    /**
     * Set an attribute on the flowing instance using "dot" notation.
     * 
     * @param  mixed  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function set($key, $value): static
    {
        data_set($this->attributes, $key, $value);
        
        return $this;
    }
    
    /**
     * Fill the flowing instance with an array of attributes.
     * 
     * @param  iterable  $attributes
     * 
     * @return static
     */
    public function fill($attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Get an attribute from the flowing instance.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function value($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        return value($default);
    }
    
    /**
     * Get all of the attributes from the flowing instance.
     * 
     * @param  mixed  $keys
     * 
     * @return array
     */
    public function all($keys = null): array
    {
        $data = $this->data();
        
        if ( ! $keys) {
            return $data;
        }
        
        $results = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($results, $key, Arr::get($data, $key));
        }

        return $results;
    }

    /**
     * Get data from the flowing instance.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    protected function data($key = null, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * Get the attributes from flowing instance.
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Convert flowing instance to an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
     * 
     * @return mixed
     */
    public function jsonSerialize(): mixed
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
    public function toJson($options = 0): string
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
    public function offsetExists($offset): bool
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
    public function offsetGet($offset): mixed
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
    public function offsetSet($offset, $value): void
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
    public function offsetUnset($offset): void
    {
        unset($this->{$offset});
    }

    /**
	 * Magic method. 
     * 
     * Searches for the given variable and returns its value.
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
	 * Magic method. 
     * 
     * Calls [$this->set] with the same parameters.
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
	 * Magic method. 
     * 
     * Determines if a variable is set.
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
	 * Magic method. 
     * 
     * Unsets a given variable.
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
     * Magic method.
     * 
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