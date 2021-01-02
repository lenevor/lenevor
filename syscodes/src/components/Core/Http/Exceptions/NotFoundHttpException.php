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
 * @since       0.7.3
 */

namespace Syscodes\Core\Http\Exceptions;

/**
 * It is activated when the server could not find the requested content.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class NotFoundHttpException extends HttpSpecializedException
{
	/**
	 * Get the HTTP status code.
	 * 
	 * @var int $code
	 */
	protected $code = 404;
	
	/**
	 * Get the HTTP message.
	 * 
	 * @var string $message
	 */
	protected $message = 'Not Found';

	/**
	 * Get the title page exception.
	 * 
	 * @var string $title
	 */
	protected $title = 'Not Found';
}