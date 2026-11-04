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

use Syscodes\Components\Support\Manager;

/**
 * This class manage the use any of maintenancedriver allows.
 */
class MaintenanceModeManager extends Manager
{
    /**
     * Create an instance of the file based maintenance driver.
     *
     * @return \Syscodes\Components\Core\Maintenance\FileMaintenanceMode
     */
    protected function createFileDriver(): FileMaintenanceMode
    {
        return new FileMaintenanceMode();
    }

    /**
     * Create an instance of the array based maintenance driver.
     *
     * @return \Syscodes\Components\Core\Maintenance\ArrayMaintenanceMode
     */
    protected function createArrayDriver(): ArrayMaintenanceMode
    {
        return new ArrayMaintenanceMode();
    }

    /**
     * Create an instance of the cache based maintenance driver.
     *
     * @return \Syscodes\Components\Core\Maintenance\CacheMaintenanceMode
     *
     * @throws \Syscodes\Components\Contracts\Container\BindingResolutionException
     */
    protected function createCacheDriver(): CacheMaintenanceMode
    {
        return new CacheMaintenanceMode(
            $this->container->make('cache'),
            $this->config->get('app.maintenance.store') ?: $this->config->get('cache.default'),
            'Syscodes\Components:Core\Maintenance:down'
        );
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('app.maintenance.driver', 'file');
    }
}