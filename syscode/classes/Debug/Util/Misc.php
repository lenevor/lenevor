<?php 

namespace Syscode\Debug\Util;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */
class Misc
{
    /**
     * Can we at this point in time send HTTP headers?
     * Currently this checks if we are even serving an HTTP request,
     * as opposed to running from a command line.
     * 
     * If we are serving an HTTP request, we check if it's not too late.
     * 
     * @return bool
     */
    public static function sendHeaders()
    {
        return isset($_SERVER["REQUEST_URI"]) && ! headers_sent();
    }
}