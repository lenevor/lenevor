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

namespace Syscodes\Components\Contracts\Cache;

use Closure;
use Syscodes\Components\Contracts\Cache\Store;

/**
 * Get function for generate a cache store instance by name.
 */
interface Factory
{
    /**
     * Get a cache driver instance.
     * 
     * @param  string|null
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function driver(?string $driver = null);

    /**
     * Get a cache store instance by name.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function store(?string $store = null);

    /**
     * Create a new cache repository with the given implementation.
     * 
     * @param  \Syscodes\Components\Contracts\Cache\Store  $store
     *
     * @return \Syscodes\Components\Cache\CacheRepository
     */
    public function getRepository(Store $store);

    /**
     * Get the default cache driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string;

    /**
     * Set the default cache driver name.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function setDefaultDriver(string $name): void;

    /**
     * Register a custom driver creator Closure.
     * 
     * @param  string  $driver
     * @param  \Closure  $callback
     * 
     * @return static
     */
    public function extend(string $driver, Closure $callback): static;
}