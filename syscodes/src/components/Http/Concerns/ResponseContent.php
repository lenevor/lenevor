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

namespace Syscodes\Components\Http\Concerns;

use Throwable;
use Symfony\Component\HttpFoundation\HeaderBag;
use Syscodes\Components\Http\Exceptions\HttpResponseException;

/**
 * Loads the response trait of headers, status code and content message.
 */
trait ResponseContent
{    
    /**
     * The exception that triggered the error response (if applicable).
     * 
     * @var \Exception|null $exception
     */
    protected $exception;

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
     * Get the status text for the response.
     *
     * @return string
     */
    public function statusText()
    {
        return $this->statusText;
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
     * @return static
     */
    public function header($key, $values, $replace = true): static
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  \Symfony\Component\HttpFoundation\HeaderBag|array  $headers
     * 
     * @return static
     */
    public function withHeaders($headers): static
    {
        if ($headers instanceof HeaderBag) {
            $headers = $headers->all();
        }

        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param  \Syscodes\Components\Http\Cookie|mixed  $cookie
     * 
     * @return static
     */
    public function cookie($cookie): static
    {
        return $this->withCookie(...func_get_args());
    }

    /**
     * Add a cookie to the response.
     *
     * @param  \Syscodes\Components\Http\Cookie|mixed  $cookie
     * 
     * @return static
     */
    public function withCookie($cookie): static
    {
        if (is_string($cookie) && function_exists('cookie')) {
            $cookie = cookie(...func_get_args());
        }

        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Get the callback of the response.
     *
     * @return string|null
     */
    public function getCallback()
    {
        return $this->callback ?? null;
    }

    /**
     * Sets the exception to the response.
     * 
     * @param  \Throwable  $e
     * 
     * @return static
     */
    public function withException(Throwable $e): static
    {
        $this->exception = $e;

        return $this;
    }

    /**
     * Throws the response in a HttpResponseException instance.
     * 
     * @throws \Syscodes\Components\Http\Exceptions\HttpResponseException
     */
    public function throwResponse()
    {
        throw new HttpResponseException($this);
    }
}