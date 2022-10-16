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

use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Contracts\Auth\StateGuard;
use Syscodes\Components\Contracts\Auth\Authenticatable;
use Syscodes\Components\Contracts\Auth\SupportedBasicAuth;
use Syscodes\Components\Auth\Concerns\GuardAuthenticationUser;

/**
 * Capture the user data using a session.
 * 
 * @author Alexander Campo <jalexam@gmail.com> 
 */
class SessionGuard implements StateGuard, SupportedBasicAuth
{
    use GuardAuthenticationUser,
        Macroable;    
    
    /**
     * The name of the guard. Typically "web".
     *
     * Corresponds to guard name in authentication configuration.
     *
     * @var string
     */
    protected $name;

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Syscodes\Components\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * The number of minutes that the "remember me" cookie should be valid for.
     *
     * @var int
     */
    protected $rememberDuration = 2628000;

    /**
     * The session used by the guard.
     *
     * @var \Syscodes\Components\Contracts\Session\Session
     */
    protected $session;

    /**
     * The component cookie creator service.
     *
     * @var \Syscodes\Components\Contracts\Cookie\QueueingFactory
     */
    protected $cookie;

    /**
     * The request instance.
     *
     * @var \Syscodes\Component\Http\Request
     */
    protected $request;

    /**
     * The event dispatcher instance.
     *
     * @var \Syscodes\Components\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * Indicates if a token user retrieval has been attempted.
     *
     * @var bool
     */
    protected $recallAttempted = false;

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $credentials = []): bool
    {
        return false;
    }

    /**
     * {@inheritdoc} 
     */
    public function basic($field = 'email', $extraConditions = [])
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function onceBasic($field = 'email', $extraConditions = [])
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        return false;
    }    
    /**
     * {@inheritdoc}
     */
    public function once(array $credentials = []): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function login(Authenticatable $user, $remember = false): void
    {

    }    
    /**
     * {@inheritdoc}
     */
    public function loginUsingId($id, $remember = false)
    {

    }    
    /**
     * {@inheritdoc}
     */
    public function onceUsingId($id)
    {

    }    
    /**
     * {@inheritdoc}
     */
    public function viaRemember(): bool
    {
        return false;
    }    
    /**
     * {@inheritdoc}
     */
    public function logout(): void
    {

    }
}