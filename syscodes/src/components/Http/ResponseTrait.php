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
 * @since       0.1.1
 */

namespace Syscodes\Http;

use Throwable;
use Syscodes\Http\Exceptions\HttpResponseException;

/**
 * Loads the response trait of headers, status code and content message.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
     * @var \Syscodes\Http\Headers $headers
     */
	public $headers;

    /**
     * Gets the protocol Http.
     * 
     * @var string $protocol
     */
    protected $protocol;

    /**
     * The server array.
     * 
     * @var array $server
     */
    protected $server;

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
     * @param  string  $key  The header name
     * @param  string  $values  The value or an array of values
     * @param  bool  $replace  If you want to replace the value exists by the header
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
     * @param  \Throwable  $e
     * 
     * @return $this
     */
    public function withException(Throwable $e)
    {
        $this->exception = $e;

        return $this;
    }

    /**
     * Throws the response in a HttpResponseException instance.
     * 
     * @throws \Syscodes\Http\Exceptions\HttpResponseException
     */
    public function throwResponse()
    {
        throw new HttpResponseException($this);
    }
}