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

namespace Syscodes\Components\Auth\Access;

use Syscodes\Components\Auth\Access\Exceptions\AuthorizationException;

/**
 * Show the response message.
 * 
 * @author Alexander Campo <jalexcam@gmai.com>
 */
class Response
{
    /**
     * Indicates whether the response was allowed.
     * 
     * @var bool $allowed
     */
    protected $allowed;

    /**
     * The response code.
     * 
     * @var mixed $code
     */
    protected $code;

    /**
     * The response message.
     * 
     * @var string $message
     */
    protected $message;

    /**
     * Constructor. Create a new Responsen class instance.
     * 
     * @param  bool  $allowed
     * @param  string  $message
     * @param  mixed  $code
     * 
     * @return void
     */
    public function __construct($allowed, string $message, $code = '')
    {
        $this->code    = $code;
        $this->allowed = $allowed;
        $this->message = $message;     
    }
    
    /**
     * Create a new "allow" Response.
     * 
     * @param  string|null  $message
     * @param  mixed  $code
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public static function allow($message = null, $code = null)
    {
        return new static(true, $message, $code);
    }
    
    /**
     * Create a new "deny" Response.
     * 
     * @param  string|null  $message
     * @param  mixed  $code
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     */
    public static function deny($message = null, $code = null)
    {
        return new static(false, $message, $code);
    }
    
    /**
     * Determine if the response was allowed.
     * 
     * @return bool
     */
    public function allowed(): bool
    {
        return $this->allowed;
    }
    
    /**
     * Determine if the response was denied.
     * 
     * @return bool
     */
    public function denied(): bool
    {
        return ! $this->allowed();
    }
    
    /**
     * Get the response message.
     * 
     * @return string|null
     */
    public function message()
    {
        return $this->message;
    }
    
    /**
     * Get the response code / reason.
     * 
     * @return mixed
     */
    public function code()
    {
        return $this->code;
    }
    
    /**
     * Throw authorization exception if response was denied.
     * 
     * @return \Syscodes\Components\Auth\Access\Response
     * 
     * @throws \Syscodes\Components\Auth\Access\Exceptions\AuthorizationException
     */
    public function authorize()
    {
        if ($this->denied()) {
            throw (new AuthorizationException($this->message(), $this->code()))
                ->setResponse($this)
                ->withStatus($this->status);
        }
        
        return $this;
    }
    
    /**
     * Set the HTTP response status code.
     * 
     * @param  null|int  $status
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
     * Get the HTTP status code.
     * 
     * @return int|null
     */
    public function status()
    {
        return $this->status;
    }
    
    /**
     * Convert the response to an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed(),
            'message' => $this->message(),
            'code' => $this->code(),
        ];
    }
    
    /**
     * Magic method.
     * 
     * Get the string representation of the message.
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->message();
    }
}