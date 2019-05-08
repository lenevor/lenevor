<?php 

namespace Syscode\Debug\Engine;

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
class Formatter
{
    /**
     * Returns all basic information about the exception in a simple array.
     * 
     * @param  \Syscode\Debug\Engine\Supervisor  $supervisor
     * 
     * @return array
     */
    public static function formatExceptionAsDataArray($supervisor)
    {
        $exception = $supervisor->getException();

        $response = [
            'class'   => get_class($exception),
            'code'    => $exception->getCode(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTrace(),
        ];

        if ($exception->getPrevious())
        {
            $response = [$response];
            $newError = static::formatExceptionAsDataArray($exception->getPrevious());
            array_unshift($response, $newError);
        }

        return $response;
    }
}