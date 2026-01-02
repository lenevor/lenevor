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
 * HttpException.
 */
class HttpException extends LenevorException
{
	/**
	 * Loader the Status Code HTTP.
	 * 
	 * @var int $code
	 */
	protected $code;

	/**
	 * Loader the headers HTTP.
	 * 
	 * @var array $headers 
	 */
	protected $headers;

	/**
	 * Initialize constructor. 
	 * 
	 * @param  int  $statusCode
	 * @param  string  $message  
	 * @param  \Throwable  $previous 
	 * @param  array  $headers
	 * @param  int  $code
	 * 
	 * @return void
	 */
	public function __construct(
		int $statusCode, 
		?string $message = null, 
		?Throwable $previous = null, 
		array $headers = [], 
		?int $code = 0
	) {
		$this->headers = $headers;
		$this->code    = $statusCode;
				
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get Status Code headers.
	 * 
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->code;
	}
	
	/**
	 * Get response headers.
	 * 
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}
	
	/**
	 * Set response headers.
	 * 
	 * @param  array  $headers  Response headers
	 * 
	 * @return mixed
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
	}
}