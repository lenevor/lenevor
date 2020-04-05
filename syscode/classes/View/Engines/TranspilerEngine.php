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

namespace Syscode\View\Engines;

use Exception;
use ErrorException;
use Syscode\View\Transpilers\TranspilerInterface;

/**
 * The file PHP engine.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class TranspilerEngine extends PhpEngine
{
    /**
     * The Plaze transpiler instance.
     * 
     * @var \Syscode\View\Transpilers\TranspilerInterface $transpiler
     */
    protected $transpiler;

    /**
     * A stack of the last transpiled templates.
     * 
     * @var array $lastCompiled
     */
    protected $lastTranspiled = [];

    /**
     * Constructor. Create a new Plaze view engine instance.
     * 
     * @param  \Syscode\View\Transpilers\TranspilerInterface  $transpiler
     * 
     * @return void
     */
    public function __construct(TranspilerInterface $transpiler)
    {
        $this->transpiler = $transpiler;
    }

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
        $this->lastTranspiled[] = $path;

        if ($this->transpiler->isExpired($path))
        {
            $this->transpiler->transpile($path);
        }

        $transpiled = $this->transpiler->getTranspilePath($path);
        
        $output = parent::get($transpiled, $data);
        
        array_pop($this->lastTranspiled);
        
        return $output;
    }

    /**
     * Handle a view exception.
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
        $e = new ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

        parent::handleViewException($e, $obLevel);
    }

    /**
     * Get the exception message for an exception.
     * 
     * @param  \Exception  $e
     * 
     * @return string
     */
    protected function getMessage(Exception $e)
    {
        return $e->getMessage().' (View: '.realpath(last($this->lastTranspiled)).')';
    }

    /**
     * Get the transpiler implementation.
     * 
     * @return \Syscode\View\Transpilers\TranspilerInterface
     */
    public function getTranspiler()
    {
        return $this->transpiler;
    }
}