<?php 

namespace Syscode\Core\Bootstrap;

use Exception;
use Syscode\Contracts\Debug\Handler;
use Syscode\Contracts\Core\Application;
use Syscode\Debug\Handlers\{ PlainTextHandler, PleasingPageHandler, JsonResponseHandler };

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
class BootHandleExceptions
{
    /**
     * The application implementation.
     *
     * @var \Syscode\Contracts\Core\Application $app
     */
    protected $app;
    
    /**
     * Bootstrap the given application.
     *
     * @param  \Syscode\Contracts\Core\Application  $app
     * 
     * @return void
     */
    public function bootstrap(Application $app)
    {
       $this->app = $app;

       $this->renderException();
    }
    
    /**
     * Handle an incoming HTTP request.
     * 
     * @return void
     */
    protected function renderException()
    {
        try
        {
            $exception = $this->getExceptionHandler();
            $exception->on();
            $exception->pushHandler(new PleasingPageHandler);
        }
        catch(Exception $e)
        {
            return $e;
        }
    }
    
    /**
     * Get an instance of the exception handler.
     *
     * @return \Syscode\Contracts\Debug\Handler
     */
    protected function getExceptionHandler()
    {    
        return $this->app->make(Handler::class);
    }
}