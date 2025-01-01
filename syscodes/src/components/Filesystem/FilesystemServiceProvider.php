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

namespace Syscodes\Components\Filesystem;

use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the classes from the container of services.
 */
class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()            
    {
        $this->registerLocalFilesystem();
        
        $this->registerFilesystemManager();
    }

    /**
     * Register the local filesystem implementation.
     * 
     * @return void
     */
    protected function registerLocalFilesystem()
    {
        $this->app->singleton('files', fn () => new Filesystem);
    }
    
    /**
     * Register the driver based filesystem.
     * 
     * @return void
     */
    protected function registerFilesystemManager()
    {
        $this->registerManager();
        
        $this->app->singleton('filesystem.disk', fn ($app) => $app['filesystem']->disk($this->getDefaultDriver()));
    }

    
    /**
     * Register the filesystem manager.
     * 
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('filesystem', fn ($app) => new FilesystemManager($app));
    }
    
    /**
     * Get the default file driver.
     * 
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }
}