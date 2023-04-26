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

namespace Syscodes\Components\Support;

use Countable;

/**
 * Gets the messages in the default bag.
 */
class ViewErrorBag implements Countable
{
    /**
     * The array of the view error bags.
     * 
     * @var array $bags
     */
    protected $bags = [];
    
    /**
     * Checks if a named MessageBag exists in the bags.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function hasBag($key = 'default'): bool
    {
        return isset($this->bags[$key]);
    }
    
    /**
     * Get a MessageBag instance from the bags.
     * 
     * @param  string  $key
     * 
     * @return \Syscodes\Components\Contracts\Support\MessageBag
     */
    public function getBag($key)
    {
        return Arr::get($this->bags, $key) ?: new MessageBag;
    }
    
    /**
     * Get all the bags.
     * 
     * @return array
     */
    public function getBags(): array
    {
        return $this->bags;
    }
    
    /**
     * Add a new MessageBag instance to the bags.
     * 
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\MessageBag  $bag
     * 
     * @return static
     */
    public function put($key, $bag): static
    {
        $this->bags[$key] = $bag;
        
        return $this;
    }

    /**
     * Determine if the default message bag has any messages.
     *
     * @return bool
     */
    public function any(): bool
    {
        return $this->count() > 0;
    }
    
    /**
     * Get the number of messages in the default bag.
     * 
     * @return int
     */
    public function count(): int
    {
        return $this->getBag('default')->count();
    }
    
    /**
     * Magic method.
     * 
     * Dynamically call methods on the default bag.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->getBag('default')->$method(...$parameters);
    }
    
    /**
     * Magic method.
     * 
     * Dynamically access a view error bag.
     * 
     * @param  string  $key
     * 
     * @return \Syscodes\Components\Contracts\Support\MessageBag
     */
    public function __get($key)
    {
        return $this->getBag($key);
    }
    
    /**
     * Magic method.
     * 
     * Dynamically set a view error bag.
     * 
     * @param  string  $key
     * @param  \Syscodes\Components\Contracts\Support\MessageBag  $value
     * 
     * @return void
     */
    public function __set($key, $value)
    {
        $this->put($key, $value);
    }
    
    /**
     * Magic method.
     * 
     * Convert the default bag to its string representation.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getBag('default');
    }
}