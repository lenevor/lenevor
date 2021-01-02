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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.1
 */

namespace Syscodes\Contracts\View;

use Syscodes\Contracts\Support\Renderable;

/**
 * Returns the data by reference to have values imposed by the user.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
	 * @return $this
	 */
	public function assign($key, $value = null);
	
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
	 * @return $this
	 */
	public function bind($key, & $value);
}