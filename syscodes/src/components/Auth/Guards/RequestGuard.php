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

namespace Syscodes\Components\Auth\Guards;

use Syscodes\Components\Http\Request;
use Syscodes\Components\Contracts\Auth\Guard;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Contracts\Auth\UserProvider;
use Syscodes\Components\Auth\Concerns\GuardAuthenticationUser;

/**
 * Capture the user data using a request.
 * 
 * @author Alexander Campo <jalexam@gmail.com> 
 */
class RequestGuard implements Guard
{
    use GuardAuthenticationUser,
        Macroable;

    /**
     * The guard callback.
     * 
     * @var \callable $callback
     */
    protected $callback;

    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request $request
     */
    protected $request;

    /**
     * Constructor. The create new RequestGuard class instance.
     * 
     * @param  \callable  $callback
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Contracts\Auth\UserProvider|null  $provider
     * 
     * @return void
     */
    public function __construct(callable $callback, Request $request, UserProvider $provider = null)
    {
        $this->callback = $callback;
        $this->request  = $request;
        $this->provider = $provider; 
    }
    
    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ( ! is_null($this->user)) {
            return $this->user;
        }
        
        return $this->user = call_user_func(
            $this->callback, $this->request, $this->getProvider()
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate(array $credentials = []): bool
    {
        return ! is_null((new static(
            $this->callback, $credentials['request'], $this->getProvider()
        ))->user());
    }
    
    /**
     * Set the current request instance.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return self
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        
        return $this;
    }
}