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
 * @since       0.6.0
 */

namespace Syscodes\View\Engines;

use Exception;
use Throwable;
use Syscodes\Contracts\View\Engine;
use Syscodes\Debug\FatalExceptions\FatalThrowableError;

/**
 * The file PHP engine.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class PhpEngine implements Engine
{
    /**
     * Get the evaluated contents of the view.
     * 
     * @param  string  $path
     * @param  array  $data
     * 
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     * 
     * @param  string  $__path
     * @param  array  $__data
     * 
     * @return string
     */
    protected function evaluatePath($__path, $__data)
    {        
        $obLevel = ob_get_level();

        ob_start();

        extract($__data, EXTR_SKIP);

        try
        {
            include $__path;
        }
        catch(Exception $e)
        {
            $this->handleViewException($e, $obLevel);
        }
        catch(Throwable $e)
        {
            $this->handleViewException(new FatalThrowableError($e), $obLevel);
        }

        return ltrim(ob_get_clean());        
    }

    /**
     * Handle a View Exception.
     * 
     * @param  \Exception  $e
     * @param  int  $obLevel
     * 
     * @return void
     * 
     * @throws \Exception
     */
    protected function handleViewException(Exception $e, $obLevel)
    {
        while(ob_get_level() > $obLevel)
        {
            ob_end_clean();
        }

        throw $e;
    }
}