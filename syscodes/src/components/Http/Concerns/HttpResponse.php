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

namespace Syscodes\Components\Http\Concerns;

use Throwable;
use Syscodes\Components\Http\Exceptions\HttpResponseException;

/**
 * Loads the response trait of headers, status code and content message.
 */
trait HttpResponse
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
     * @var \Syscodes\Components\Http\Headers|object $headers
     */
	public $headers;

    /**
     * The HTTP protocol version.
     * 
     * @var string $version
     */
    protected $version;

    /**
     * Gets the content of the response.
     * 
     * @return string
     */
    public function content(): string
    {
        return $this->getContent();
    }

    /**
     * Gets the status code for the response.
     * 
     * @return int
     */
    public function status(): int
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
     * @return self
     */
    public function header($key, $values, $replace = true): self
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }

    /**
     * Gets the Http protocol version.
     * 
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the Http protocol version.
     * 
     * @return string
     */
    public function setProtocolVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Sets the exception to the response.
     * 
     * @param  \Throwable  $e
     * 
     * @return self
     */
    public function withException(Throwable $e): self
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