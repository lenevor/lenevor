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

namespace Syscodes\Debug\Handlers;

use Throwable;

/**
 * Handler outputing plaintext error messages.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class PlainTextHandler extends MainHandler
{
    /**
     * The way in which the data sender (usually the server) can tell the recipient 
     * (the browser, in general) what type of data is being sent in this case, plain format text.
     * 
     * @return string
     */
    public function contentType()
    {
        return 'text/plain';
    }

    /**
     * Create plain text response and return it as a string.
     * 
     * @param  \Throwable  $exception
     * 
     * @return string
     */
    protected function getResponse(Throwable $exception)
    {
        return sprintf(
            "%s: %s in file %s on line %d%s\n",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $this->getTraceOutput()
        );
    }

    /**
     * Get trace output of response text.
     * 
     * @return void
     */
    protected function getTraceOutput()
    {
        $supervisor = $this->getSupervisor();
        $frames     = $supervisor->getFrames();
        
        $response   = "\nStack trace:";

        $line = count($frames);

        foreach ($frames as $frame)
        {
            $class = $frame->getClass();

            $template = "\n%3d. %s->%s() %s:%d";

            if ( ! $class)
            {
                $template = "\n%3d. %s%s() %s:%d";
            }

            $response .= sprintf(
                $template,
                $line,
                $class,
                $frame->getFunction(),
                $frame->getFile(),
                $frame->getLine()
            );

            $line--;
        }

        return $response;
    }
    
    /**
     * Given an exception and status code will display the error to the client.
     * 
     * @return void
     */
    public function handle()
    {        
        $response = $this->getResponse($this->getException());

        echo $response;

        return MainHandler::QUIT;
    }
}