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

namespace Syscodes\View\Transpilers;

/**
 * Returns the transpilation of views.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface TranspilerInterface
{
    /**
     * Get the path to the transpiled version of a view.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    public function getTranspilePath($path);

    /**
     * Determine if the given view is expired.
     * 
     * @param  string  $path
     * 
     * @return bool
     */
    public function isExpired($path);

    /**
     * Transpile the view at the given path.
     * 
     * @param  string  $path
     * 
     * @return void
     */
    public function transpile($path);
}