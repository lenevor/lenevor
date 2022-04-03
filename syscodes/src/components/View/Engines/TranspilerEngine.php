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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\View\Engines;

use Throwable;
use ErrorException;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\View\Transpilers\TranspilerInterface;

/**
 * The file PHP engine.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class TranspilerEngine extends PhpEngine
{
    /**
     * The Plaze transpiler instance.
     * 
     * @var \Syscodes\Components\View\Transpilers\TranspilerInterface $transpiler
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
     * @param  \Syscodes\Components\View\Transpilers\TranspilerInterface  $transpiler
     * @param  \Syscodes\Components\Filesystem\Filesystem|null  $files
     * 
     * @return void
     */
    public function __construct(TranspilerInterface $transpiler, Filesystem $files = null)
    {
        parent::__construct($files ?: new Filesystem);

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
    public function get($path, array $data = []): string
    {
        $this->lastTranspiled[] = $path;

        if ($this->transpiler->isExpired($path)) {
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
     * @param  \Throwable  $e
     * @param  int  $obLevel
     * 
     * @return void
     * 
     * @throws \Throwable 
     */
    protected function handleViewException(Throwable $e, $obLevel): void
    {
        $e = new ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

        parent::handleViewException($e, $obLevel);
    }

    /**
     * Get the exception message for an exception.
     * 
     * @param  \Throwable  $e
     * 
     * @return string
     */
    protected function getMessage(Throwable $e): string
    {
        return $e->getMessage().' (View: '.realpath(lastItem($this->lastTranspiled)).')';
    }

    /**
     * Get the transpiler implementation.
     * 
     * @return \Syscodes\Components\View\Transpilers\TranspilerInterface
     */
    public function getTranspiler()
    {
        return $this->transpiler;
    }
}