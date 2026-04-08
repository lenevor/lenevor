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
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $path  Path filename
     * @param  \Syscodes\Components\Contracts\Support\Arrayable|array  $data  Array of values
     * @param  array  $mergeData  Array of merge data

     * @return \Syscodes\Components\Contracts\View\View
     */
    public function file($path, $data = [], $mergeData = []);

    /**
	 * Global and local data are merged and extracted to create local variables within the view file.
	 * Renders the view object to a string.
	 *
	 * @example $output = $view->make();
	 *
	 * @param  string  $view  View filename
	 * @param  \Syscodes\Components\Contracts\Support\Arrayable|array  $data  Array of values
	 * @param  array  $mergeData  Array of merge data
	 *
	 * @return \Syscodes\Components\Contracts\View\View
	 */
    public function make($view, $data = [], $mergeData = []);

    /**
	 * Call the creator for a given view.
	 * 
	 * @param  \Syscodes\View\View  $view
	 * 
	 * @return void
	 */
	public function callCreator(View $view): void;

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
	 * Add a new namespace to the loader.
	 * 
	 * @param  string  $namespace
	 * @param  string|array  $hints
	 * 
	 * @return static
	 */
	public function addNamespace($namespace, $hints): static;

    /**
	 * Replace the namespace hints for the given namespace.
	 * 
	 * @param  string  $namespace
	 * @param  string|array  $hints
	 * 
	 * @return self
	 */
	public function replaceNamespace($namespace, $hints): self;
}