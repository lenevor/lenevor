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
 * Null cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class NullStore implements Store
{
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
        //
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $seconds): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function decrement($key, $value = 1)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return '';
    }
}