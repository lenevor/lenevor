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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http\Exceptions;

use Throwable;
use Syscodes\Components\Core\Http\Exceptions\HttpException;

/**
 * The request entity returns when it is longer than the limits 
 * defined by the server.
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
	 * @param  string|null  $message   
	 * @param  \Throwable|null  $previous  
	 * @param  int  $code 
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