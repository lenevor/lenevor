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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\View\Exceptions;

use ErrorException;
use Syscodes\Components\Support\Reflector;
use Syscodes\Components\Container\Container;

/**
 * ViewException.
 */
class ViewException extends ErrorException
{
    /**
     * Report the exception.
     * 
     * @return bool|null
     */
    public function report()
    {
        $exception = $this->getPrevious();
        
        if (Reflector::isCallable($reportCallable = [$exception, 'report'])) {
            return Container::getInstance()->call($reportCallable);
        }
        
        return false;
    }
    
    /**
     * Render the exception into an HTTP response.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Http\Response|null
     */
    public function render($request)
    {
        $exception = $this->getPrevious();
        
        if ($exception && method_exists($exception, 'render')) {
            return $exception->render($request);
        }
    }
}