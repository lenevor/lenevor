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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.1
 */

namespace Syscode\Http;

use Countable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Headers class is a container for HTTP headers.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Headers implements IteratorAggregate, Countable
{
    /**
	 * An array of HTTP headers.
	 *
	 * @var array $herders
	 */
    protected $headers = [];
    
    /**
     * Specifies the directives for the caching mechanisms in both 
     * the requests and the responses.
     * 
     * @var array $cacheControl
     */
    protected $cacheControl = [];

    /**
     * Constructor. The Headers class instance.
     * 
     * @param  array  $headers
     * 
     * @return void
     */
    public function __construct(array $headers = []) 
    {
        foreach ($headers as $key => $values)
		{
			$this->set($key, $values);
		}
	}

    /**
	 * Returns all the headers.
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->headers;
	}
	
	/**
	 * Returns the parameter keys.
	 * 
	 * @return array An array of parameter keys
	 */
	public function keys()
	{
		return array_keys($this->all());
	}

	/**
	 * Replaces the current HTTP headers by a new set.
	 * 
	 * @param  array  $headers
	 * 
	 * @return void
	 */
	public function replace(array $headers = [])
	{
		$this->headers = [];
		$this->add($headers);
	}

	/**
	 * Adds multiple header to the queue.
	 *
	 * @param  array  $headers  The header name
	 *
	 * @return mixed
	 */
	public function add(array $headers)
	{
		foreach ($headers as $key => $values) 
		{
			$this->set($key, $values);
		}

		return $this;
	}

    /**
	 * Gets a header value by name.
	 *
	 * @param  string       $key      The header name, or null for all headers
	 * @param  string|null  $default  The default value
     * @param  bool         $option   Whether to return the option value or all header values (true by default)
	 *
	 * @return mixed
	 */
	public function get($key, $default =  null, $option = true)
	{
		$key = str_replace('_', '-', strtolower($key));
		$headers = $this->all();
		
		if ( ! array_key_exists($key, $headers))
		{
			if (null === $default)
			{
				return $option ? null : [];
			}
			
			return $option ? $default : [$default];
		}
		
		if ($option)
		{
			return count($headers[$key]) ? $headers[$key][0] : $default;
		}
		
		return $headers[$key];		
    }

	/**
	 * Sets a header by name.
	 * 
	 * @param  string  $key      The header name
	 * @param  string  $values   The value or an array of values
	 * @param  bool    $replace  If you want to replace the value exists by the header, 
	 * 					         it is not overwritten (true by default) / overwritten when it is false
	 *
	 * @return $this
	 */
	public function set($key, $values, $replace = true)
	{
		$key     = str_replace('_', '-', strtolower($key));
		$headers = $this->all();

		if (is_array($values))
		{
			$values = array_values($values);

			if (true === $replace || ! isset($headers[$key]))
			{
				$headers[$key] = $values;
			}
			else
			{
				$headers[$key] = array_merge($headers[$key], $values);
			}
		}
		else
		{
			if (true === $replace || ! isset($headers[$key]))
			{
				$headers[$key] = [$values];
			}
			else
			{
				$headers[$key][] = $values;
			}
		}

		return $this;
	}

	/**
	 * Returns true if the HTTP header is defined.
	 * 
	 * @param  string  $key  The HTTP header
	 * 
	 * @return bool  true if the parameter exists, false otherwise
	 */
	public function has($key)
	{
		return array_key_exists(str_replace('_', '-', strtolower($key)), $this->all());
	}

	/**
	 * Removes a header.
	 * 
	 * @param  string  $name  The header name
	 * 
	 * @return mixed
	 */
	public function remove($key)
	{
		$key = str_replace('_', '-', strtolower($key));

		unset($this->headers[$key]);

		if ('cache-control' === $key)
		{
			$this->cacheControl = [];
		}
	}
	
	/**
	 * Returns an iterator for headers.
	 * 
	 * @return \ArrayIterator An \ArrayIterator instance
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->headers);
	}
	
	/**
	 * Returns the number of headers.
	 * 
	 * @return int The number of headers
	 */
	public function count()
	{
		return count($this->headers);
	}
}