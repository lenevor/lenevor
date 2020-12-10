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
 * @since       0.1.0
 */

namespace Syscodes\Debug\FrameHandler;

use Syscodes\Debug\Util\TemplateHandler;

/**
 * Returns all basic information about the exception in a simple array and
 * in a plain text.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Formatter
{
    /**
     * Returns all basic information about the exception in a simple array.
     * 
     * @param  \Syscodes\Debug\Engine\Supervisor  $supervisor
     * 
     * @return array
     */
    public static function formatExceptionAsDataArray(Supervisor $supervisor)
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

    /**
     * Returns all basic information about the exception in a plain text.
     * 
     * @param  \Syscodes\Debug\Engine\Supervisor  $supervisor
     * 
     * @return string
     */
    public static function formatExceptionAsPlainText(Supervisor $supervisor)
    {
        $message  = $supervisor->getException()->getMessage();
        $frames   = $supervisor->getFrames();
        $template = new TemplateHandler;

        $plainText  = $supervisor->getExceptionName();
        $plainText .= " thrown with message: \n";
        $plainText .= ucfirst($message);
        $plainText .= '"'."\n\n";

        $plainText .= "Stacktrace:\n";

        foreach ($frames as $i => $frame)
        {
            $plainText .= "#".(count($frames) - $i)." ";
            $plainText .= $frame->getClass() ?: '';
            $plainText .= $frame->getClass() && $frame->getFunction() ? ":" : '';
            $plainText .= $frame->getFunction() ?: '';
            $plainText .= ' in ';
            $plainText .= $template->cleanPath($frame->getFile()) ?: "<#unknown>";
            $plainText .= ' : ';
            $plainText .= (int) $frame->getLine()."\n";
        }

        return $plainText;
    }
}