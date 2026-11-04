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

namespace Syscodes\Components\Core\Maintenance;

use Syscodes\Components\Contracts\Cache\Factory;
use Syscodes\Components\Contracts\Cache\Repository;
use Syscodes\Components\Contracts\Core\MaintenanceMode;

/**
 * Get the driver cache maintenance mode.
 */
class CacheMaintenanceMode implements MaintenanceMode
{
    /**
     * The cache factory.
     *
     * @var \Syscodes\Components\Contracts\Cache\Factory
     */
    protected $cache;

    /**
     * The cache key to use when storing maintenance mode information.
     *
     * @var string
     */
    protected $key;

    /**
     * The cache store that should be utilized.
     *
     * @var string
     */
    protected $store;

    /**
     * Create a new cache based maintenance mode implementation.
     *
     * @param  \Syscodes\Components\Contracts\Cache\Factory  $cache
     * @param  string  $store
     * @param  string  $key
     * @return void
     */
    public function __construct(Factory $cache, string $store, string $key)
    {
        $this->cache = $cache;
        $this->store = $store;
        $this->key = $key;
    }

    /**
     * Take the application down for maintenance.
     *
     * @param  array  $payload
     * @return void
     */
    public function activate(array $payload): void
    {
        $this->getStore()->put($this->key, $payload);
    }

    /**
     * Take the application out of maintenance.
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->getStore()->delete($this->key);
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function active(): bool
    {
        return $this->getStore()->has($this->key);
    }

    /**
     * Get the data array which was provided when the application was placed into maintenance.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->getStore()->get($this->key);
    }
    
    /**
     * Get the cache store to use.
     *
     * @return \Syscodes\Components\Contracts\Cache\Repository
     */
    protected function getStore(): Repository
    {
        return $this->cache->store($this->store);
    }
}