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

namespace Syscodes\Components\Contracts\View;

use Syscodes\Components\Contracts\Support\Renderable;

/**
 * Returns the data by reference to have values imposed by the user.
 */
interface View extends Renderable
{
	/**
	 * The view data will be extracted.
	 * 
	 * @return array
	 */
	public function getArrayData(): array;

	/**
	 * Get the sections of the rendered view.
	 * 
	 * @return array
	 * 
	 * @throws \Throwable
	 */
	public function renderSections();

	/**
	 * Add a piece of data to the view.
	 * 
	 * @example  $view->assign($content, $data);
	 * 
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * 
	 * @return self
	 */
	public function assign($key, $value = null): self;
	
	/**
	 * Assigns a value by reference. The benefit of binding is that values can be altered
	 * without re-setting them. It is also possible to bind variables before they have values.
	 * Assigned values will be available as a variable within the view file:
	 * 
	 * @example  $view->bind('ref', $bar);
	 * 
	 * @param  string  $key  Variable name
	 * @param  mixed  $value  Referenced variable
	 * 
	 * @return self
	 */
	public function bind($key, & $value): self;

	/**
	 * Get the array of view data.
	 * 
	 * @return array
	 */
	public function getData(): array;

	/**
	 * Get the name of the view.
	 * 
	 * @return string
	 */
	public function getView(): string;

	/**
	 * Get the path to the view file.
	 * 
	 * @return string
	 */
	public function getPath(): string;

	/**
	 * Set the path to the view file.
	 * 
	 * @param  string  $path
	 * 
	 * @return void
	 */
	public function setPath($path): void;

	/**
	 * Assigns a variable by name. Assigned values will be available as a
	 * variable within the view file:
	 *
	 * This value can be accessed as $var within the view
	 * @example $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * @param  string|array  $key    Variable name
	 * @param  mixed         $value  Value
	 *
	 * @return self
	 */
	public function set($key, $value = null): self;

	/**
	 * Get content as a string of HTML.
	 * 
	 * @return string
	 */
	public function toHtml();
}