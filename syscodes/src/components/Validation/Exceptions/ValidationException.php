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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation\Exceptions;

use RuntimeException;

/**
 * The validation to exception class instance.
 */
class ValidationException extends RuntimeException
{
    /**
     * The name of the error bag.
     *
     * @var string
     */
    public $errorBag;

    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 422;

    /**
     * The path the client should be redirected to.
     *
     * @var string
     */
    public $redirectTo;

    /**
     * The recommended response to send to the client.
     *
     * @var \Syscodes\Components\Http\Response|null
     */
    public $response;

    /**
     * The validator instance.
     *
     * @var \Syscodes\Components\Contracts\Validation\Validator
     */
    public $validator;

    /**
     * Create a new exception instance.
     *
     * @param  \Sysocdes\Components\Contracts\Validation\Validator  $validator
     * @param  \Syscodes\Components\Http\Response|null  $response
     * @param  string  $errorBag
     * 
     * @return void
     */
    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct(static::summarize($validator));

        $this->response = $response;
        $this->errorBag = $errorBag;
        $this->validator = $validator;
    }

    /**
     * Create an error message summary from the validation errors.
     *
     * @param  \Syscodes\Components\Contracts\Validation\Validator  $validator
     * 
     * @return string
     */
    protected static function summarize($validator)
    {
        $messages = $validator->errors()->all();
        $message  = array_shift($messages);        

        return $message;
    }

    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->validator->errors()->messages();
    }

    /**
     * Set the HTTP status code to be used for the response.
     *
     * @param  int  $status
     * 
     * @return static
     */
    public function status($status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set the error bag on the exception.
     *
     * @param  string  $errorBag
     * 
     * @return static
     */
    public function errorBag($errorBag): static
    {
        $this->errorBag = $errorBag;

        return $this;
    }

    /**
     * Set the URL to redirect to on a validation error.
     *
     * @param  string  $url
     * 
     * @return static
     */
    public function redirectTo($url): static
    {
        $this->redirectTo = $url;

        return $this;
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Syscodes\Components\Http\Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}