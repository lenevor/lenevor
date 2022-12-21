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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

 namespace Syscodes\Components\Auth\Access\Exceptions;

use Exception;
use Throwable;
use Syscodes\Components\Auth\Access\Response;

/**
 * AuthorizationException.
 */
class AuthorizationException extends Exception
{
    /**
     * The Code HTTP response.
     * 
     * @var int $code
     */
    protected $code;

    /**
     * The response from the gate.
     * 
     * @var \Syscodes\Components\Auth\Access\Response $response
     */
    protected $response;
    
    /**
     * The HTTP response status code.
     * 
     * @var int|null $status
     */
    protected $status;
    
    /**
     * Constructor. Create a new authorization exception instance.
     * 
     * @param  string|null  $message
     * @param  mixed  $code
     * @param  \Throwable|null  $previous
     * 
     * @return void
     */
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? 'This action is unauthorized.', 0, $previous);
        
        $this->code = $code ?: 0;
    }
    
    /**
     * Get the response from the gate.
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public function response()
    {
        return $this->response;
    }
    
    /**
     * Set the response from the gate.
     * 
     * @param  \Syscodes\Components\Auth\Access\Response  $response
     * 
     * @return self
     */
    public function setResponse($response): self
    {
        $this->response = $response;
        
        return $this;
    }
    
    /**
     * Set the HTTP response status code.
     * 
     * @param  int|null  $status
     * 
     * @return self
     */
    public function withStatus($status): self
    {
        $this->status = $status;
        
        return $this;
    }
    
    /**
     * Set the HTTP response status code to 404.
     * 
     * @return self
     */
    public function asNotFound(): self
    {
        return $this->withStatus(404);
    }
    
    /**
     * Determine if the HTTP status code has been set.
     * 
     * @return bool
     */
    public function hasStatus(): bool
    {
        return $this->status !== null;
    }
    
    /**
     * Get the HTTP status code.
     * 
     * @return int|null
     */
    public function status()
    {
        return $this->status;
    }
    
    /**
     * Create a deny response object from this exception.
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public function toResponse()
    {
        return Response::deny($this->message, $this->code)->withStatus($this->status);
    }
}