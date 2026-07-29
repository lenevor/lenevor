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

use ArrayIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Syscodes\Components\Contracts\Support\ValidatedData;
use Syscodes\Components\Support\Traits\Dumpable;
use Syscodes\Components\Support\Traits\InteractsWithData;
use Traversable;

/**
 * Allows the validated input.
 */
class ValidatedInput implements ValidatedData
{
    use Dumpable, InteractsWithData;

    /**
     * The underlying input.
     *
     * @var array
     */
    protected $input;

    /**
     * Constructor. Create a new validated input container.
     *
     * @param  array  $input
     * @return void
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Merge the validated input with the given array of additional data.
     *
     * @param  array  $items
     * @return static
     */
    public function merge(array $items): static
    {
        return new static(array_merge($this->all(), $items));
    }

    /**
     * Get the raw, underlying input array.
     *
     * @param  mixed  $keys
     * @return array
     */
    public function all($keys = null): array
    {
        if (! $keys) {
            return $this->input;
        }

        $input = [];

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            Arr::set($input, $key, Arr::get($this->input, $key));
        }

        return $input;
    }

    /**
     * Retrieve data from the instance.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function data($key = null, $default = null)
    {
        return $this->input($key, $default);
    }

    /**
     * Get the keys for all of the input.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->input());
    }

    /**
     * Retrieve an input item from the validated inputs.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        return data_get(
            $this->all(), $key, $default
        );
    }

    /**
     * Retrieve a file from the validated inputs.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
     */
    public function file($key, $default = null)
    {
        $value = $this->input($key, $default);

        return $value instanceof UploadedFile ? $value : $default;
    }

    /**
     * Dump the items.
     *
     * @param  mixed  ...$keys
     * @return static
     */
    public function dump(...$keys): static
    {
        dump($keys !== [] ? $this->only($keys) : $this->all());

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Magic method.
     * 
     * Dynamically access input data.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->input($name);
    }

    /**
     * Magic method.
     * 
     * Dynamically set input data.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->input[$name] = $value;
    }

    /**
     * Magic method.
     * 
     * Determine if an input item is set.
     *
     * @param  string  $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->exists($name);
    }

    /**
     * Magic method.
     * 
     * Remove an input item.
     *
     * @param  string  $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->input[$name]);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->exists($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->input($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->input[] = $value;
        } else {
            $this->input[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->input[$key]);
    }

    /**
     * Get an iterator for the input.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->input);
    }
}