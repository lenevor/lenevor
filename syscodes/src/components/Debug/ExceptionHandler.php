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
 */

namespace Syscodes\Debug;

use Exception;
use Throwable;
use Syscodes\Debug\FatalExceptions\FlattenException;
use Syscodes\Debug\FatalExceptions\OutOfMemoryException;

/**
 * A generic ErrorHandler for the PHP engine.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * Gets the file link format.
     * 
     * @var string $fileLinkFormat
     */
    protected $fileLinkFormat;

    /**
     * Gets an error handler.
     * 
     * @var string $handler
     */
    protected $handler;

    /**
     * Register the exception handler.
     * 
     * @param  bool  $debug
     * @param  string|null  $charset
     * @param  string|null  $fileLinkformat
     * 
     * @return static
     */
    public static function register($debug = true, $charset = null, $fileLinkFormat = null)
    {
        $handler = new static($debug, $charset, $fileLinkFormat);

        set_exception_handler([$handler, 'handle']);

        return $handler;
    }

    /**
     * Constructor. Initialize the ExceptionHandler instance.
     * 
     * @param  bool  $debug
     * @param  string|null  $charset
     * @param  string|null  $fileLinkformat
     * 
     * @return void
     */
    public function __construct(bool $debug = true, string $charset = null, $fileLinkFormat = null)
    {
        $this->debug          = $debug;
        $this->charset        = $charset ?: ini_get('default_charset') ?: 'UTF-8'; 
        $this->fileLinkFormat = $fileLinkFormat;
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
     * Sets the format for links to source files.
     * 
     * @param  string|FileLinkFormatter  $fileLinkFormat
     * 
     * @return string
     */
    public function setFileLinkFormat($fileLinkFormat)
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
     * @param \Exception|\Syscodes\Debug\FlattenExceptions\FlattenException  $exception An \Exception or \FlattenException instance
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
     * @param  \Exception|\Syscodes\Debug\FlattenExceptions\FlattenException  $exception  An \Exception or \FlattenException instance
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

    /**
     * Layout HTML for gets the content and style css.
     * 
     * @param  string  $content
     * @param  string  $styleCss
     * 
     * @return string
     */
    private function design($content, $styleCss)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta charset="{$this->charset}" />
        <meta name="robots" content="noindex">    
        <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1">
        <style>
            $styleCss
        </style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
    }

    /**
     * Gets the HTML content associated with the given exception.
     * 
     * @param  \Syscodes\Debug\FlattenExceptions\FlattenException  $exception
     * 
     * @return string  The HTML content as a string
     */
    public function getContent(FlattenException $exception)
    {
        switch ($exception->getStatusCode())
        {
            case 404:
                $title = 'Sorry, the page you are looking to could not be found';
                break;
            default:
                $title = 'Whoops, looks like something went wrong';
        }

        if ( ! $this->debug)
        {
            return <<<EOF
                <div class="container">
                    <h1>$title</h1>
                </div>
EOF;
        }

        $content = '';

        try
        {
            $count = count($exception->getAllPrevious());
            $total = $count + 1;

            foreach ($exception->toArray() as $position => $e)
            {
                $index   = $count - $position + 1;
                $class   = $this->formatClass($e['class']);
                $message = nl2br($this->escapeHtml($e['message']));
                $content .= sprintf(<<<'EOF'
                    <div class="trace">
                        <table>
                            <tr class="trace-head"><th> 
                                    <h3 class="trace-class">
                                        <span class="text-muted">(%d/%d)</span>
                                        <span class="exception_title">%s</span>
                                    </h3>
                                    <p class="break-long-words trace-message">%s</p>
                            </th></tr>
EOF
                    , $index, $total, $class, $message);

                foreach ($e['trace'] as $trace) 
                {
                    $content .= '<tr><td>';

                    if ($trace['function']) 
                    {
                        $content .= sprintf('from <span class="trace-class">%s</span><span class="trace-type">%s</span><span class="trace-method">%s</span>(<span class="trace-arguments">%s</span>)', $this->formatClass($trace['class']), $trace['type'], $trace['function'], $this->formatArgs($trace['args']));
                    }

                    if (isset($trace['file']) && isset($trace['line'])) 
                    {
                        $content .= $this->formatPath($trace['file'], $trace['line']);
                    }
                    
                    $content .= "</td></tr>\n";
                }

                $content .= "</table>\n</div>\n";
            }
        }
        catch (Exception $e)
        {
            if ($this->debug)
            {
                $e     = FlattenException::make($e);
                $title = sprintf('Exception thrown when handling an exception: (%s: %s)',
                    $e->getClass(),
                    $this->escapeHtml($e->getMessage())
                );
            }
            else
            {
                $title = 'Whoops, looks like something went wrong';
            }
        }

        return <<<EOF
            <div class="exception">
                <div class="container">
                    <div class="exception-wrapper">
                        <h1 class="break-long-words exception-message">$title</h1>
                    </div>
                </div>
            </div>

            <div class="container">
                $content
            </div>
EOF;
    }

    public function getStylesheet()
    {
        if ( ! $this->debug) {
            return <<<'EOF'
                body { background-color: #fff; color: #222; font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; overflow: hidden; }
                .container { display: flex; align-items: center; justify-content: center; height: 100vh; width: 100vw; }
                h1 { color: #586d7e; font-size: 2em; text-shadow: none; word-break: break-all; word-break: break-word; }
EOF;
        }

        return <<<'EOF'
            body { background-color: #F9F9F9; color: #222; font: 14px/1.4 Helvetica, Arial, sans-serif; margin: 0; padding-bottom: 45px; }

            a { cursor: pointer; text-decoration: none; }
            a:hover { text-decoration: underline; }
            abbr[title] { border-bottom: none; cursor: help; text-decoration: none; }

            code, pre { font: 13px/1.5 Consolas, Monaco, Menlo, "Ubuntu Mono", "Liberation Mono", monospace; }

            table, tr, th, td { background: #FFF; border-collapse: collapse; vertical-align: top; }
            table { background: #FFF; border: 1px solid #E0E0E0; box-shadow: 0px 0px 1px rgba(128, 128, 128, .2); margin: 1em 0; width: 100%; }
            table th, table td { border: solid #E0E0E0; border-width: 1px 0; padding: 8px 10px; }
            table th { background-color: #E0E0E0; font-weight: bold; text-align: left; }

            .hidden-xs-down { display: none; }
            .block { display: block; }
            .break-long-words { -ms-word-break: break-all; word-break: break-all; word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; }
            .text-muted { color: #999; }

            .container { max-width: 1024px; margin: 0 auto; padding: 0 15px; }
            .container::after { content: ""; display: table; clear: both; }

            .exception { background: #B0413E; border-bottom: 2px solid rgba(0, 0, 0, 0.1); border-top: 1px solid rgba(0, 0, 0, .3); flex: 0 0 auto; margin-bottom: 30px; }

            .exception-wrapper { display: flex; align-items: center; min-height: 70px; }
            .exception-message { flex-grow: 1; padding: 30px 0; text-shadow: none; }
            .exception-message, .exception-message a { color: #FFF; font-size: 21px; font-weight: 400; margin: 0; }
            .exception-message.long { font-size: 18px; }
            .exception-message a { border-bottom: 1px solid rgba(255, 255, 255, 0.5); font-size: inherit; text-decoration: none; }
            .exception-message a:hover { border-bottom-color: #ffffff; }

            .exception-illustration { flex-basis: 111px; flex-shrink: 0; height: 66px; margin-left: 15px; opacity: .7; }

            .trace + .trace { margin-top: 30px; }
            .trace-head .trace-class { color: #222; font-size: 18px; font-weight: bold; line-height: 1.3; margin: 0; position: relative; }

            .trace-message { font-size: 14px; font-weight: normal; margin: .5em 0 0; }

            .trace-file, .trace-file a { color: #222; margin-top: 3px; font-size: 13px; }
            .trace-class { color: #B0413E; }
            .trace-type { padding: 0 2px; }
            .trace-method { color: #B0413E; font-weight: bold; }
            .trace-args { color: #777; font-weight: normal; padding-left: 2px; }

            @media (min-width: 575px) {
                .hidden-xs-down { display: initial; }
            }
EOF;
    }

    /**
     * Gets the format class where the exception.
     * 
     * @param  string  $class
     * 
     * @return string
     */
    private function formatClass($class)
    {
        $parts = explode('\\', $class);

        return sprintf('<abbr title="%s">%s</abbr>', $class, array_pop($parts));
    }

    /**
     * Gets the path file with you line code.
     * 
     * @param  string  $path
     * @param  int  $line
     * 
     * @return string
     */
    private function formatPath($path, $line)
    {
        $file = $this->escapeHtml(preg_match('#[^/\\\\]*+$#', $path, $file) ? $file[0] : $path);
        $frmt = $this->fileLinkFormat ?: ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');

        if ( ! $frmt)
        {
            return sprintf('<span class="block trace-file">in <span title="%s%3$s"><strong>%s</strong>%s</span></span>', 
                $this->escapeHtml($path),
                $file,
                0 < $line ? ' line '.$line : ''
            );
        }

        if (is_string($frmt))
        {
            $index  = strpos($f = $frmt, '&', max(strrpos($f, '%f'), strrpos($f, '%l')) ?: strlen($f));
            $frmt = [substr($f, 0, $index)] + preg_split('/&([^>]++)>/', substr($f, $index), -1, PREG_SPLIT_DELIM_CAPTURE);

            for ($index = 1; isset($frmt[$index]); ++$index)
            {
                if (strpos($path, $k = $frmt[$index++]))
                {
                    $path = substr_replace($path, $frmt[$index], 0, strlen($k));
                    break;
                }
            }

            $data = strstr($frmt[0], ['%f' => $file, '%l' => $line]);
        }
        else
        {
            try
            {
                $data = $frmt->format($file, $line);
            }
            catch (Exception $e)
            {
                return sprintf('<span class="block trace-file-path">in <span title="%s%3$s"><strong>%s</strong>%s</span></span>', 
                        $this->escapeHtml($path), $file, 0 < $line ? ' line '.$line : '');
            }
        }

        return sprintf('<span class="block trace-file">in <a href="%s" title="Go to source"><b>%s</b>%s</a></span>', 
                $this->escapeHtml($data), $file, $line > 0 ? ' line '.$line : '');
    }

    /**
     * Formats an array as a string.
     * 
     * @param  array  $args
     * 
     * @return string
     */
    private function formatArgs(array $args)
    {
        $result = [];

        foreach ($args as $key => $value)
        {
            if ($value[0] === 'object')
            {
                $formatValue = sprintf('<em>(Object)</em>%s', $this->formatClass($value[1]));
            }
            elseif ($value[0] === 'array')
            {
                $formatValue = sprintf('<em>(array)</em>%s', is_array($value[1]) ? $this->formatArgs($value[1]) : $value[1]);
            }
            elseif ($value[0] === 'null')
            {
                $formatValue = '<em>Null</em>';
            }
            elseif ($value[0] === 'boolean')
            {
                $formatValue = '<em>'.strtolower(var_export($value[1], true)).'</em>';
            }
            elseif ($value[0] === 'resource')
            {
                $formatValue = '<em>resource</em>';
            }
            else
            {
                $formatValue = str_replace("\n", '', $this->escapeHtml(var_export($value[1], true)));
            }

            $result[] = is_int($key) ? $formatValue : sprintf("'%s' => %s", $this->escapeHtml($key), $formatValue);
        }

        return implode(', ', $result);
    }

    /**
     * Gets HTML-encode as a string.
     * 
     * @param  string  $string
     * 
     * @return string
     */
    private function escapeHtml($string)
    {
        return htmlspecialchars($string, ENT_COMPAT | ENT_SUBSTITUTE, $this->charset);
    }
}