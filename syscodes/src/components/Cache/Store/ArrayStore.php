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

namespace Syscodes\Components\Cache\Store;

use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Cache\concerns\CacheMultipleKeys;

/**
 * Array cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ArrayStore implements Store
{
    use CacheMultipleKeys,
        InteractsWithTime;

    /**
     * The array storaged value.
     * 
     * @var array $storage
     */
    protected $storage = [];

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ( ! isset($this->storage[$key])) return;

        $item = $this->storage[$key];

        $expiration = $item['expiration'] ?? 0;

        if ($expiration !== 0 && $this->currentTime() > $expiration) {
            $this->delete($key);

            return;
        }

        return $item['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds): bool
    {
        $this->storage[$key] = [
            'value'      => $value,
            'expiration' => $this->calcExpiration($seconds)
        ];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        if ( ! isset($this->storage[$key])) {
            $this->forever($key, $value);

            return $this->storage[$key]['value'];
        }

        $this->storage[$key]['value'] = ((int) $this->storage[$key]['value']) + $value;

        return $this->storage[$key]['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if (array_key_exists($key, $this->storage))
        {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): bool
    {
        $this->storage = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return '';
    }

    /**
     * Gets the expiration time of the key.
     * 
     * @param  int  $seconds
     * 
     * @return int
     */
    protected function calcExpiration($seconds): int
    {
        return $this->toTimestamp($seconds);
    }

    /**
     * Gets the UNIX timestamp for the given number of seconds.
     * 
     * @param  int  $seconds
     * 
     * @return int
     */
    protected function toTimestamp($seconds): int
    {
        return $seconds > 0 ? $this->availableAt($seconds) : 0;
    }
}