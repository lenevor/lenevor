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
 * @since       0.5.0
 */

namespace Syscode\Routing;

/**
 * Create a new route file for register instance.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class RouteFileRegister
{
    /**
     * The router instance.
     *
     * @var \Syscode\Routing\Router $router
     */
    protected $router;

    /**
     * Create a new route file registrar instance.
     *
     * @param  \Syscode\Routing\Router  $router

     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Require the given routes file.
     *
     * @param  string  $routes
     * 
     * @return void
     */
    public function register($routes)
    {
        $router = $this->router;
        
        require $routes;
    }
}