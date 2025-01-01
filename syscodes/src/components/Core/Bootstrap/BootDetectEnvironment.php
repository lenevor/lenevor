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

namespace Syscodes\Components\Core\Bootstrap;

use Exception;
use Syscodes\Components\Dotenv\Dotenv;
use Syscodes\Components\Support\Environment;
use Syscodes\Components\Contracts\Core\Application;

/**
 * Initialize boot of a ParseEnv instance.
 */
class BootDetectEnvironment
{
    /**
     * The application implementation.
     *
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * Bootstrap the given application.
     *
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->detectEnvironmentFile($app);
        
        try {
            $this->createEnv($app)->load();
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return bool
     */
    protected function detectEnvironmentFile($app)
    {
        $environment = Environment::get('APP_ENV');

        if ( ! $environment) {
            return;
        }

        $this->setEnvironmentFilePath(
            $app, $app->environmentFile().'.'.$environment
        );
    }

    /**
     * Load a custom environment file.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * @param  string  $file
     * 
     * @return bool
     */
    protected function setEnvironmentFilePath($app, $file)
    {
        if (is_file($app->environmentPath().'/'.$file)) {
            $app->setEnvironmentFile($file);

            return true;
        }

        return false;
    }

    /**
     * Create a ParseEnv instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * 
     * @return \Syscodes\Components\Dotenv\Dotenv
     */
    protected function createEnv($app)
    {
        return Dotenv::create(
                    Environment::getRepositoryCreator(),
                    $app->environmentPath(),
                    $app->environmentFile()
               );
    }
}