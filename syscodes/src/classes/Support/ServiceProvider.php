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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.4.0
 */

namespace Syscodes\Support;

/**
 * Loads all the services provider of system.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
abstract class ServiceProvider 
{
    /**
     * The application instance.
     * 
     * @var \Syscodes\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * Constructor. Create a new service provider instance.
     * 
     * @param  \Syscodes\Contracts\Core\Applicacion  $app
     * 
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register() {}
}