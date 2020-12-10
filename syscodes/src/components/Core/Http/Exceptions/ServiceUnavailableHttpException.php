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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.7.3
 */

namespace Syscodes\Core\Http\Exceptions;

use Throwable;

/**
 * It is activated when the server is not ready to handle the request. 
 * Common causes may be that the server is down due to maintenance or 
 * is overloaded. These responses should be used for temporary conditions 
 * and the HTTP Retry-After header: should, if possible, contain the estimated 
 * time before the recovery of the service.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class ServiceUnavailableHttpException extends HttpSpecializedException
{
	/**
	 * Get the HTTP status code.
	 * 
	 * @var int $code
	 */
	protected $code = 503;
	
	/**
	 * Get the HTTP message.
	 * 
	 * @var string $message
	 */
	protected $message = 'Unprocessable Entity';

	/**
	 * Get the title page exception.
	 * 
	 * @var string $title
	 */
	protected $title = 'Unprocessable Entity';

	/**
	 * Initialize constructor. 
	 * 
	 * @param  int|string|null  $retryAfter  The number of seconds or HTTP-date after 
	 * 										 which the request may be retried  (null by default)
	 * @param  string|null $message  (null by default)
	 * @param  \Throwable|null  $previous  (null by default)
	 * @param  array  $headers
	 * 
	 * @return void
	 */
	public function __construct($retryAfter = null, string $message = null, Throwable $previous = null, array $headers = [])
	{		
		if ($retryAfter)
		{
			$headers['Retry-After'] = $retryAfter;
		}

		parent::__construct($this->message, $previous, $headers);
	}
}