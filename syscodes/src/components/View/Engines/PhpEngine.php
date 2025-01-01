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
use Syscodes\Components\Contracts\View\Engine;
use Syscodes\Components\Filesystem\Filesystem;

/**
 * The file PHP engine.
 */
class PhpEngine implements Engine
{
    /**
     * The Filesystem instance
     * 
     * @var \Syscodes\Components\Filesystem\Filesystem $files
     */
    protected $files;
    
    /**
     * Constructor. Create new a PhpEngine instance.
     * 
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * 
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
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
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     * 
     * @param  string  $path
     * @param  array  $data
     * 
     * @return string
     */
    protected function evaluatePath($path, $data)
    {
        $obLevel = ob_get_level();
        
        ob_start();

        try {
            $this->files->getRequire($path, $data);
        } catch(Throwable $e) {
            return $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());        
    }

    /**
     * Handle a View Exception.
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
        while(ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}