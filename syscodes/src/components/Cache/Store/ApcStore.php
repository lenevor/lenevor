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

/**
 * Apc cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ApcStore implements Store
{
    /**
     * The APC wrapper instance.
     * 
     * @var \Syscodes\Components\Cache\Store\ApcWrapper $apc
     */
    protected $apc;

    /**
     * A string that should be prepended to keys.
     * 
     * @var string $prefix
     */
    protected $prefix;

    /**
     * Constructor. The new APC store instance.
     * 
     * @param  \Syscodes\Components\Cache\Store\ApcWrapper  $apc
     * @param  string  $prefix
     * 
     * @return void
     */
    public function __construct(ApcWrapper $apc, $prefix = '')
    {
        $this->apc    = $apc;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $value = $this->apc->get($this->prefix.$key);

        if (false !== $value) {
            return $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds): bool
    {
        return $this->apc->put($this->prefix.$key, $value, $seconds);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        return $this->apc->increment($this->prefix.$key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->apc->decrement($this->prefix.$key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->apc->delete($this->prefix.$key);
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
    public function flush()
    {
        return $this->apc->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}