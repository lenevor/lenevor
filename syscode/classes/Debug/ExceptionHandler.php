<?php 

namespace Syscode\Core\Debug;

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
 * @since       0.1.0
 */
class ExceptionHandler
{
    /**
     * Gets activation of debugging.
     * 
     * @var bool $debug
     */
    protected $debug;

    /**
     * Gets the charset. By default UTF-8.
     * 
     * @var string $charset
     */
    protected $charset;

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
}