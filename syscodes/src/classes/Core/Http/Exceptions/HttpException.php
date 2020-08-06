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
 * @since       0.1.0
 */

namespace Syscodes\Core\Http\Exceptions;

use Throwable;

/**
 * HttpException.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class HttpException extends LenevorException
{
	/**
	 * Loader the Status Code HTTP.
	 * 
	 * @var int $statusCode
	 */
	protected $statusCode;

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
	 * 
	 * @throws \Syscodes\Core\Http\Exceptions\LenevorException
	 */
	public function __construct(int $statusCode, string $message = null, Throwable $previous = null, array $headers = [], ?int $code = 0)
	{
		$this->headers    = $headers;
		$this->statusCode = $statusCode;
		
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get Status Code headers.
	 * 
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
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