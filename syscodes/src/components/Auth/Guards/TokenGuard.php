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
use Syscodes\Components\Contracts\Auth\UserProvider;
use Syscodes\Components\Auth\Concerns\GuardAuthenticationUser;

/**
 * Capture the user data using a token header. 
 * 
 * @author Alexander Campo <jalexam@gmail.com> 
 */
class TokenGuard implements Guard
{
    use GuardAuthenticationUser;
    
    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request $request
     */
    protected $request;
    
    /**
     * The name of the query string item from the request containing the API token.
     * 
     * @var string $inputKey
     */
    protected $inputKey;
    
    /**
     * The name of the token "column" in persistent storage.
     * 
     * @var string $storageKey
     */
    protected $storageKey;
    
    /**
     * Indicates if the API token is hashed in storage.
     * 
     * @var bool $hash
     */
    protected $hash = false;
    
    /**
     * Constructor. Create a new authentication guard.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\UserProvider  $provider
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  string  $inputKey
     * @param  string  $storageKey
     * @param  bool  $hash
     * 
     * @return void
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        $inputKey = 'api_token',
        $storageKey = 'api_token',
        $hash = false
    ) {
        $this->hash = $hash;
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->storageKey = $storageKey;
    }
    
    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ( ! is_null($this->user)) {
            return $this->user;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function validate(array $credentials = []): bool
    {
        return false;
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