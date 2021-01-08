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

namespace Syscodes\Http\Exceptions;

use Throwable;
use Syscodes\Core\Http\Exceptions\HttpException;

/**
 * The request entity returns when it is longer than the limits 
 * defined by the server.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class PostTooLargeHttpException extends HttpException
{
	/**
	 * Get the HTTP status code.
	 * 
	 * @var int $code
	 */
	protected $code = 413;

	/**
	 * Initialize constructor. 
	 * 
	 * @param  string|null  $message  (null by default) 
	 * @param  \Throwable|null  $previous  (null by default)
	 * @param  int  $code  (0 by default)
	 * @param  array  $headers
	 * 
	 * @return void
	 */
	public function __construct(
		string $message = null, 
		Throwable $previous = null, 
		int $code = 0, 
		array $headers = []
	) {
        parent::__construct(
			$this->code, 
			$message, 
			$previous, 
			$headers, 
			$code
		);
	}
}