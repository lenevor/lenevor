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

namespace Syscodes\Components\Collections\Traits;

use JsonSerializable;
use Syscodes\Components\Contracts\Support\Arrayable;

/**
 * Trait.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait Enumerates
{
    /**
     * Create a new collection instance if the value isn't one already.
     * 
     * @param  mixed  $items
     * 
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     * 
     * @param \callable|mixed  $callable
     * 
     * @return static
     */
    public function reject($callback = true)
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable
                ? ! $callback($value, $key)
                : $value != $callback;
        });
    }

    /**
     * Determine if the given value is callable, but not a string.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }
    
    /**
     * Determine if the collection is not empty.
     * 
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }
    
    /**
     * Get the collection of items as JSON.
     * 
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    
    /**
     * Convert the object into something JSON serializable.
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }
            
            return $value;
        }, $this->all());
    }
}