<?php 

namespace Syscode\Core\Debug;

use Exception;
use Throwable;
use Syscode\Debug\FlattenExceptions\{ 
    FlattenException, 
    OutOfMemoryException 
};

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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.1
 */
class ExceptionHandler
{
    /**
     * Gets caught buffer of memory.
     * 
     * @var mixed $caughtBuffer
     */
    protected $caughtBuffer;

    /**
     * Gets caught lenght of buffer.
     * 
     * @var int $caughtLength
     */
    protected $caughtLength;

    /**
     * Gets the charset. By default UTF-8.
     * 
     * @var string $charset
     */
    protected $charset;

    /**
     * Gets activation of debugging.
     * 
     * @var bool $debug
     */
    protected $debug;

    /**
     * Gets an error handler.
     * 
     * @var string $handler
     */
    protected $handler;

    /**
     * Register the exception handler.
     * 
     * @param  bool         $debug
     * @param  string|null  $charset
     * 
     * @return void
     */
    public static function register($debug = true, $charset = null)
    {
        $handler = new static($debug, $charset);

        set_exception_handler([$handler, 'handle']);

        return $handler;
    }

    /**
     * Constructor. Initialize the ExceptionHandler instance.
     * 
     * @param  bool         $debug
     * @param  string|null  $charset
     * 
     * @return void
     */
    public function __construct(bool $debug = true, string $charset = null)
    {
        $this->debug   = $debug;
        $this->charset = $charset ?: init_set('default_charset') ?: 'UTF-8'; 
    }

    /**
     * Sets a user exception handler.
     * 
     * @param  \Callable  $handler
     * 
     * @return \Callable|null
     */
    public function setHandler(Callable $handler)
    {
        $oldHandler    = $this->handler;
        $this->handler = $handler;

        return $oldHandler;
    }

    /**
     * Sends a response for the given Exception.
     * 
     * How does it work:
     * First, the exception is handled by system exception handler, then by the user exception handler.
     * The latter has priority and any exit from the first one is canceled.
     * 
     * @param  \Exception  $exception
     * 
     * @return void
     */
    public function handler(Exception $exception)
    {
        if ($this->handler === null && $exception instanceof OutOfMemoryException)
        {
            $this->sendPhpResponse($exception);
        }

        $caughtLength = $this->caughtLength = 0;

        ob_start(function ($buffer) {
            $this->caughtBuffer = $buffer;

            return '';
        });

        $this->sendPhpResponse($exception);

        if (isset($this->caughtBuffer[0]))
        {
            ob_start(function ($buffer) {
                if ($this->caughtLength)
                {
                    $cleanBuffer = substr_replace($buffer, '', 0, $this->caughtLength);

                    if (isset($cleanBuffer[0]))
                    {
                        $buffer = $cleanBuffer;
                    }
                }

                return $buffer;
            });

            echo $this->caughtBuffer;
            // Return the length of the output buffer.
            $caughtLength = ob_get_length();
        }

        $this->caughtBuffer = null;

        try
        {
            ($this->handler)($exception);
            $caughtLength = $this->caughtLength;
        }
        catch (Exception $e)
        {
            if ( ! $caughtLength)
            {
                throw $e;
            }
        }
    }

    /**
     * Sends the error associated with the given Exception as a plain PHP response. 
     * 
     * This method uses plain php functions as header and echo to generate the output 
     * response.
     * 
     * @param \Exception|\Syscode\Debug\FlattenExceptions\FlattenException  $exception An \Exception or \FlattenException instance
     * 
     * @return string  The HTML content as a string 
     */
    public function sendPhpResponse($exception)
    {
        if ( ! $exception instanceof FlattenException)
        {
            $exception = FlattenException::make($exception);
        }

        if ( ! headers_sent())
        {
            header(sprintf('HTTP/1.0 %s', $exception->getStatusCode()));

            foreach ($exception->getHeaders() as $name => $value)
            {
                header($name.':'.$value, false);
            }

            header('Content-Type: text/html; charset='.$this->charset);
        }

        echo $this->design($this->getContent($exception), $this->getStylesheet());
    }

    /**
     * Gets the full HTML content associated with the given exception.
     * 
     * @param  \Exception|\Syscode\Debug\FlattenExceptions\FlattenException  $exception  An \Exception or \FlattenException instance
     * 
     * @return string  The HTML content as a string 
     */
    public function getHtmlResponse($exception)
    {
        if ( ! $exception instanceof FlattenException)
        {
            $exception = FlattenException::make($exception);
        }

        echo $this->design($this->getContent($exception), $this->getStylesheet());
    }

    private function design($content, $styleCss)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex">    
        <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1">
        <style>
            $styleCss
        <style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
    }
}