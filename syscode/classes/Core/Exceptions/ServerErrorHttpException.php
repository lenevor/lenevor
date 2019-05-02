<?php 

namespace Syscode\Core\Exceptions;

use Throwable;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.6.0
 */
class ServerErrorHttpException extends HttpException
{
	/**
	 * Initialize constructor. 
	 * 
	 * @param  string      $message  
	 * @param  \Throwable  $previous
	 * @param  int         $code
	 * @param  array       $headers
	 * 
	 * @return void
	 */
	public function __construct($message = null, Throwable $previous = null, int $code = 0, array $headers = [])
	{		
		parent::__construct(500, $message, $previous, $headers, $code);
	}
}