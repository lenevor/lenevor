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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

use Syscodes\Components\Contracts\Support\Collectable;

/**
 * Allows a new collection proxy instance.
 */
class HigherOrderCollectionProxy
{
    /**
     * The collection being operated on.
     * 
     * @var \Syscodes\Components\Contracts\Support\Collectable $collection
     */
    protected $collection;
    
    /**
     * The method being proxied.
     * 
     * @var string $method
     */
    protected $method;

    /**
     * Constructor. Create a new collection proxy instance.
     * 
     * @param  \Syscodes\Components\Contracts\Support\Collectable  $Collection
     * @param  string  $method
     * 
     * @return void
     */
    public function __construct(Collectable $collection, string $method)
    {
        $this->collection = $collection;
        $this->method     = $method;
    }
    
    /**
     * Magic method.
     * 
     * Gets the proxy accessing an attribute onto the collection items.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return $this->collection->{$this->method}(function ($value) use ($key) {
            return is_array($value) ? $value[$key] : $value->{$key};
        });
    }

    /**
     * Magic method. 
     * 
     * Dynamically pass method calls to the collection.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}