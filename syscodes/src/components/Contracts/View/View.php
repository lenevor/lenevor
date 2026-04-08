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

use Syscodes\Components\Contracts\Support\Renderable;

/**
 * Returns the data by reference to have values imposed by the user.
 */
interface View extends Renderable
{
	/**
	 * Add a piece of data to the view.
	 * 
	 * @example  $view->assign($content, $data);
	 * 
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * 
	 * @return static
	 */
	public function assign($key, $value = null): static;

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
	public function name(): string;
}