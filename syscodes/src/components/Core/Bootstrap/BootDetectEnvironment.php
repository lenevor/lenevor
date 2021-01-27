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
 * @since       0.1.2
 */

namespace Syscodes\Core\Bootstrap;

use Exception;
use Syscodes\Dotenv\Dotenv;
use Syscodes\Contracts\Core\Application;
use Syscodes\Dotenv\Repository\RepositoryCreator;

/**
 * Initialize boot of a ParseEnv instance.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BootDetectEnvironment
{
    /**
     * The application implementation.
     *
     * @var \Syscodes\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * Bootstrap the given application.
     *
     * @param  \Syscodes\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $this->detectEnvironmentFile($app);
        try
        {
            $this->createEnv($app)->load();
        }
        catch (Exception $e)
        {
            //
        }
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     * 
     * @param  \Syscodes\Contracts\Core\Application  $app
     * 
     * @return bool
     */
    protected function detectEnvironmentFile($app)
    {
        $environment = env('APP_ENV');

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
     * @param  \Syscodes\Contracts\Core\Application  $app
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
     * @param  \Syscodes\Contracts\Core\Application  $app
     * 
     * @return \Syscodes\Dotenv\Dotenv
     */
    protected function createEnv($app)
    {
        return Dotenv::create(
               new RepositoryCreator,
               $app->environmentPath(),
               $app->environmentFile()
        );
    }
}