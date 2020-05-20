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

namespace Syscodes\Contracts\Debug;

use Exception;

/**
 * Handles an exception into an HTTP response for to render.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface ExceptionHandler
{
    /**
     * Report or log an exception.
     * 
     * @param  \Exception  $e
     * 
     * @return mixed
     */
    public function report(Exception $e);

    /**
     * Render an exception into an HTTP response.
     * 
     * @param  \Syscodes\Http\Request  $request
     * @param  \Exception  $e
     * 
     * @return \Syscodes\Http\Response
     */
    public function render($request, Exception $e);
}