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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\View;

/**
 * Returns the location of a view.
 */
interface ViewFinder
{
    /**
     * Hint path delimiter value.
     * 
     * @var string
     */
    const HINT_PATH_DELIMITER = '::';

    /**
     * Get the complete location of the view.
     * 
     * @param  string  $name
     *
     * @return string
     */
    public function find($name): string;

    /**
     * Add a namespace hint to the finder.
     * 
     * @param  string  $namespace
     * @param  string|array  $hints
     * 
     * @return void
     */
    public function addNamespace($namespace, $hints): void;

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * 
     * @return void
     */
    public function prependNamespace($namespace, $hints): void;

    /**
     * Replace the namespace hints for the given namespace.
     * 
     * @param  string  $namespace
     * @param  string|array  $hints
     * 
     * @return void
     */
    public function replaceNamespace($namespace, $hints): void;

    /**
     * Register an extension with the view finder.
     *
     * @param  string  $extension
     * 
     * @return void
     */
    public function addExtension($extension): void;

    /**
     * Flush the cache of located views.
     *
     * @return void
     */
    public function flush(): void;
}