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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Concerns;

use Syscodes\Components\Collections\Arr;

/**
 * Trait InteractsWithInput.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait InteractsWithInput
{
    /**
     * Retrieve a server variable from the request.
     * 
     * @param  string|null  $key
     * @param  string|null  $default
     * 
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        return $this->retrieveItem('server', $key, $default);
    }

    /**
     * Determine if a header is set on the request.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function hasHeader($key): bool
    {
        return ! is_null($this->header($key));
    }

    /**
     * Retrieve a header from the request.
     * 
     * @param  string|null  $key
     * @param  string|null  $default
     * 
     * @return mixed
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }

    /**
     * Determine if a cookie is set on the request.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function hasCookie($key): bool
    {
        return ! is_null($this->cookie($key));
    }

    /**
     * Retrieve a cookie from the request.
     * 
     * @param  string|null  $key
     * @param  string|null  $default
     * 
     * @return mixed
     */
    public function cookie($key = null, $default = null)
    {
        return $this->retrieveItem('cookies', $key, $default);
    }

    /**
     * Retrieve a 'request' item from the request.
     * 
     * @param  string|null  $key
     * @param  string|null  $default
     * 
     * @return string|array|null
     */
    public function post($key = null, $default = null)
    {
        return $this->retrieveItem('request', $key, $default);
    }

    /**
     * Retrieve a 'request' item from the request.
     * 
     * @param  string|null  $key
     * @param  string|null  $default
     * 
     * @return string|array|null
     */
    public function file($key = null, $default = null)
    {
        return Arr::get($this->allFiles(), $key, $default);
    }

    /**
     * Retrieve a 'queryString' item from the request.
     * 
     * @param  string|null  $key
     * @param  string|null  $default
     * 
     * @return string|array|null
     */
    public function queryString($key = null, $default = null)
    {
        return $this->retrieveItem('queryString', $key, $default);
    }

    /**
     * Adds parameters.
     * 
     * @param  string|array  $key
     * 
     * @return array
     */
    public function add($key): array
    {
        $key = is_array($key) ? $key : [$key];

        return $this->getInputSource()->add($key);
    }

    /**
     * Get all of the input and files for the request.
     * 
     * @return array
     */
    public function all(): array
    {
        return array_merge_recursive($this->input(), $this->allFiles());
    }

    /**
     * Retrieve an input item from the request.
     * 
     * @param  string|null  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        $input = $this->getInputSource()->all();

        return Arr::get($input, $key, $default);
    }

    /**
     * Get an array of all of the files on the request.
     * 
     * @return array
     */
    public function allFiles(): array
    {
        return $this->files->all();
    }

    /**
     * Replace the input for the current request.
     * 
     * @param  string|array  $key
     * 
     * @return void
     */
    public function replace($key)
    {
        $key = is_array($key) ? $key : [$key];

        return $this->getInputSource()->replace($key);
    }

    /**
     * Get the keys for all of the input and files.
     * 
     * @return array
     */
    public function keys(): array
    {
        return array_merge($this->input(), $this->files->keys());
    }

    /**
     * Gets a request containing a given input item key.
     * 
     * @param  string|array  $key
     * 
     * @return bool
     */
    public function has($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        $array = $this->all();

        foreach ($keys as $value) {
            if ( ! Arr::has($array, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
	 * Remove a parameter array item.
	 *
	 * @param  string  $key 
	 *
	 * @return void
	 */
	public function remove($key)
    {
        return $this->getInputSource()->remove($key);
    }

    /**
     * Retrieve a parameter item from a given source.
     * 
     * @param  string  $source
     * @param  string  $key
     * @param  mixed  $default
     * 
     * @return mixed
     */
    protected function retrieveItem($source, $key, $default)
    {
        if (null === $key) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }
}