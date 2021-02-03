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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the App class facade.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 *
 * @method static string basePath(string $path = '')
 * @method static string bootstrapPath(string $path = '')
 * @method static string configPath(string $path = '')
 * @method static string databasePath(string $path = '')
 * @method static string resourcePath(string $path = '')
 * @method static string storagePath(string $path = '')
 * @method static void bootstrapWith(array $bootstrappers) 
 * @method static string setEnvironmentPath(string $path)
 * @method static string setEnvironmentFile(string $file)
 * @method static string environmentPath()
 * @method static string environmentFile()
 * @method static string environmentFilePath()
 * @method static bool hasBeenBootstrapped()
 * @method static void registerConfiguredProviders()
 * @method static \Syscodes\Support\ServiceProvider register(\Syscodes\Support\ServiceProvider $provider, bool $force = false)
 * @method static \Syscodes\Support\ServiceProvider resolveProviderClass(string $provider)
 * @method static void isBooted()
 * @method static void boot()
 * @method static void booting(callable $callback)
 * @method static void booted(callable $callback)
 * 
 * @see \Syscodes\Contracts\Core\Application
 */
class App extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}