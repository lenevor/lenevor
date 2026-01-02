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

use Syscodes\Components\View\View;

/**
 * Gets the contents of a view.
 */
interface Factory
{
    /**
	 * Check existance view file.
	 * 
	 * @param  string  $view
	 *
	 * @return bool
	 */
    public function exists($view): bool;

    /**
     * Global and local data are merged and extracted to create local variables within the view file.
	 * Renders the view object to a string.
	 *
	 * @example $output = $view->make();
	 *
	 * @param  string  $view  View filename
	 * @param  array  $data  Array of values
	 *
	 * @return \Syscodes\Components\Contracts\View\View
     */
    public function make($view, $data = []);

    /**
	 * Call the creator for a given view.
	 * 
	 * @param  \Syscodes\View\View  $view
	 * 
	 * @return void
	 */
	public function callCreator(View $view): void;

    /**
	 * Get the extension to engine bindings.
	 * 
	 * @return array
	 */
	public function getExtensions(): array;


    /**
	 * Add a piece of shared data to the environment.
	 * 
	 * @param  array|string  $key
	 * @param  mixed|null  $value  
	 * 
	 * @return mixed
	 */
	public function share($key, $value = null);

    /**
	 * Replace the namespace hints for the given namespace.
	 * 
	 * @param  string  $namespace
	 * @param  string|array  $hints
	 * 
	 * @return self
	 */
	public function replaceNamespace($namespace, $hints): self;

    /**
	 * Increment the rendering counter.
	 * 
	 * @return int
	 */
	public function increment(): int;

    /**
	 * Decrement the rendering counter.
	 * 
	 * @return int
	 */
	public function decrement(): int;

    /**
	 * Check if there are no active render operations.
	 * 
	 * @return bool
	 */
	public function doneRendering(): bool;

    /**
	 * Flush all of the parser state like sections.
	 * 
	 * @return void
	 */
	public function flushState(): void;

    /**
	 * Flush all of the section contents if done rendering.
	 * 
	 * @return void
	 */
	public function flushStateIfDoneRendering(): void;

    /**
	 * Get all of the shared data for the environment.
	 * 
	 * @return array
	 */
	public function getShared(): array;
}