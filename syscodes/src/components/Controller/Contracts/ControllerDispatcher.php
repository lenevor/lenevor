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
 * @since       0.5.2
 */

namespace Syscodes\Controller\Contracts;

use Syscodes\Routing\Route;

/**
 * Dispatch requests when called a given controller and method.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */

interface ControllerDispatcher
{
    /**
     * Dispatch a request to a given controller and method.
     * 
     * @param  \Syscodes\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * 
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method);
}