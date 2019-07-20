<?php

namespace Syscode\Http;

use Exception;
use Syscode\Http\Exceptions\HttpResponseException;

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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.1
 */
trait ResponseTrait 
{
    /**
	 * The content of the response.
	 * 
	 * @var string $content
	 */
    protected $content = null;
    
    /**
     * The exception that triggered the error response (if applicable).
     * 
     * @var \Exception|null $exception
     */
    protected $exception;

    /**
	 * The Headers class instance.
	 *
	 * @var \Syscode\Http\Headers $headers
	 */
	public $headers;
    
	/**
	 * The parameter array.
	 * 
	 * @var array  $parameters
	 */
	protected $parameters;

    /**
     * Gets the protocol Http.
     * 
     * @var string $protocol
     */
    protected $protocol;

    /**
     * Gets the content of the response.
     * 
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Gets the status code for the response.
     * 
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * Sets a header on the response.
     * 
     * @param  string  $key      The header name
	 * @param  string  $values   The value or an array of values
	 * @param  bool    $replace  If you want to replace the value exists by the heade
     * 
     * @return array
     */
    public function header($key, $values, $replace = true)
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }

    /**
     * Sets the exception to the response.
     * 
     * @param  \Exception  $e
     * 
     * @return $this
     */
    public function withException(Exception $e)
    {
        $this->exception = $e;

        return $this;
    }
}