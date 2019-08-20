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
 * @since       0.3.0
 */

namespace Syscode\Cache;

use Closure;
use ArrayAccess;
use Syscode\Contracts\Cache\Store;
use Syscode\Support\InteractsWithTime;

/**
 * Begin executing operations of storage data if the store supports it.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class CacheRepository implements ArrayAccess
{
    use InteractsWithTime;

    /**
     * The cache store implementation.
     * 
     * @var \Syscode\Contracts\Cache\Store $store
     */
    protected $store;

    /**
     * The default number of seconds to store items.
     * 
     * @var int $time
     */
    protected $time = 3600;

    /**
     * Constructor. Create a new cache repository instance.
     * 
     * @param  \Syscode\Contracts\Cache\Store  $store
     * 
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Attempts to retrieve an item from the cache by key.
     * 
     * @param  string  $key      Cache item name
     * @param  mixed   $default  (null by default)
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->store->get($key);

        if ( ! is_null($default))
        {
            return $value;
        }

        return value($default);
    }

    /**
     * Saves an item to the cache store.
     * 
     * @param  string    $key    Cache item name
     * @param  mixed     $value  The data to save 
     * @param  int|null  $ttl    Time To Live, in seconds (null by default)
     */
    public function save($key, $value, $ttl = null)
    {
        $seconds = $this->getSeconds($ttl);

        if ($seconds <= 0)
        {
            return $this->delete($key);
        }

        return $this->store->save($this->itemKey($key), $value, $seconds);
    }

    /**
     * Calculate the number of seconds with the given duration.
     * 
     * @param  \DateTime|\DateInterval|int  $ttl
     * 
     * @return int
     */
    protected function getSeconds($ttl)
    {
        $duration = $this->parseDateInterval($ttl);

        if ($duration instanceof DateTime)
        {
            $duration = $duration->diff($duration, false);
        }

        return (int) $duration > 0 ? $duration : 0;
    }
}