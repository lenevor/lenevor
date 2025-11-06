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

namespace Syscodes\Components\View\Engines;

use Throwable;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\View\Exceptions\ViewException;
use Syscodes\Components\Core\Http\Exceptions\HttpException;
use Syscodes\Components\View\Transpilers\TranspilerInterface;
use Syscodes\Components\Http\Exceptions\HttpResponseException;

/**
 * The file PHP engine.
 */
class TranspilerEngine extends PhpEngine
{
    /**
     * A stack of the last transpiled templates.
     * 
     * @var array $lastCompiled
     */
    protected $lastTranspiled = [];

    /**
     * The Plaze transpiler instance.
     * 
     * @var \Syscodes\Components\View\Transpilers\TranspilerInterface $transpiler
     */
    protected $transpiler;
    
    /**
     * The view paths that were compiled or are not expired, keyed by the path.
     * 
     * @var array<string, true> $transpilerOrNotExpired
     */
    protected $transpilerOrNotExpired = [];

    /**
     * Constructor. Create a new Plaze view engine instance.
     * 
     * @param  \Syscodes\Components\View\Transpilers\TranspilerInterface  $transpiler
     * @param  \Syscodes\Components\Filesystem\Filesystem|null  $files
     * 
     * @return void
     */
    public function __construct(TranspilerInterface $transpiler, ?Filesystem $files = null)
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

        if ( ! isset($this->transpilerOrNotExpired[$path]) && $this->transpiler->isExpired($path)) {
            $this->transpiler->transpile($path);
        }

        try {
            $output = $this->evaluatePath($this->transpiler->getTranspilePath($path), $data);
        } catch(ViewException $e) {
            if ( ! isset($this->transpilerOrNotExpired[$path])) {
                throw $e;
            }
            
            $this->transpiler->transpile($path);

            $output = $this->evaluatePath($this->transpiler->getTranspilePath($path), $data);
        }

        $this->transpilerOrNotExpired[$path] = true;
        
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
        if ($e instanceof HttpException || $e instanceof HttpResponseException) {
            parent::handleViewException($e, $obLevel);
        }
        
        $e = new ViewException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

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
        return $e->getMessage().' (View: '.realpath(last($this->lastTranspiled)).')';
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
    
    /**
     * Clear the cache of views that were transpiled or not expired.
     * 
     * @return void
     */
    public function eraseTranspiledOrNotExpired(): void
    {
        $this->transpilerOrNotExpired = [];
    }
}