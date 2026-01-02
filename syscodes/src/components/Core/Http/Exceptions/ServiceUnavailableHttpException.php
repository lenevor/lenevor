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

namespace Syscodes\Components\Core\Http\Exceptions;

use Throwable;

/**
 * It is activated when the server is not ready to handle the request. 
 * Common causes may be that the server is down due to maintenance or 
 * is overloaded. These responses should be used for temporary conditions 
 * and the HTTP Retry-After header: should, if possible, contain the estimated 
 * time before the recovery of the service.
 */
class ServiceUnavailableHttpException extends HttpException
{
	/**
	 * Get the HTTP status code.
	 * 
	 * @var int $code
	 */
	protected $code = 503;

	/**
	 * Initialize constructor. 
	 * 
	 * @param  int|string|null  $retryAfter  The number of seconds or HTTP-date after 
	 * 										 which the request may be retried
	 * @param  string|null $message
	 * @param  \Throwable|null  $previous
	 * @param  int  $code
	 * @param  array  $headers
	 * 
	 * @return void
	 */
	public function __construct(
		$retryAfter = null, 
		?string $message = null, 
		?Throwable $previous = null, 
		?int $code = 0,
		array $headers = []
	) {		
		if ($retryAfter) {
			$headers['Retry-After'] = $retryAfter;
		}

		parent::__construct(
			$this->code, 
			$message, 
			$previous, 
			$headers, 
			$code
		);
	}
}