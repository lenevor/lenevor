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

namespace Syscodes\Components\Cache;

use Syscodes\Components\Cache\Store\MemcachedConnector;
use Syscodes\Components\Contracts\Support\Deferrable;
use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the classes from the container of services.
 */
class CacheServiceProvider extends ServiceProvider implements Deferrable
{
    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cache', fn ($app) => (new CacheManager($app)));

        $this->app->singleton('cache.store', fn ($app) => $app['cache']->driver());

        $this->app->singleton('memcached.connector', fn () => new MemcachedConnector);
    }
    
    /**
     * Get the services provided by the provider.
     * 
     * @return array
     */
    public function provides(): array
    {
        return [
            'cache', 'cache.store', 'memcached.connector',
        ];
    }
}