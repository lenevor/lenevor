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

namespace Syscodes\Components\Auth\Guards;

use RuntimeException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Auth\Recaller;
use Syscodes\Components\Auth\Events\Login;
use Syscodes\Components\Auth\Events\Failed;
use Syscodes\Components\Auth\Events\Logout;
use Syscodes\Components\Auth\Events\Validated;
use Syscodes\Components\Auth\Events\Attempting;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Auth\Events\Authenticated;
use Syscodes\Components\Contracts\Auth\StateGuard;
use Syscodes\Components\Contracts\Session\Session;
use Syscodes\Components\Contracts\Auth\UserProvider;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Auth\Events\OtherDeviceLogout;
use Syscodes\Components\Contracts\Auth\Authenticatable;
use Syscodes\Components\Contracts\Auth\SupportedBasicAuth;
use Syscodes\Components\Auth\Concerns\GuardAuthenticationUser;
use Syscodes\Components\Contracts\Cookie\QueueingFactory as Cookie;
use Syscodes\Components\Core\Http\Exceptions\UnauthorizedHttpException;

/**
 * Capture the user data using a session. 
 */
class SessionGuard implements StateGuard, SupportedBasicAuth
{
    use GuardAuthenticationUser,
        Macroable;
        
    /**
     * The component cookie creator service.
     *
     * @var \Syscodes\Components\Contracts\Cookie\QueueingFactory
     */
    protected $cookie;
    
    /**
     * The name of the guard. Typically "web".
     *
     * Corresponds to guard name in authentication configuration.
     *
     * @var string
     */
    protected $name;

    /**
     * The event dispatcher instance.
     *
     * @var \Syscodes\Components\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Syscodes\Components\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

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
     * The number of minutes that the "remember me" cookie should be valid for.
     *
     * @var int
     */
    protected $rememberDuration = 576000;

    /**
     * The request instance.
     *
     * @var \Syscodes\Component\Http\Request
     */
    protected $request;

    /**
     * The session used by the guard.
     *
     * @var \Syscodes\Components\Contracts\Session\Session
     */
    protected $session;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * Constructor. Create a new Sessionguard class instance.
     * 
     * @param  string  $name
     * @param  \Syscodes\Components\Contracts\Auth\UserProvider  $provider
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * @param  \Syscodes\Components\Http\Request|null  $request
     * 
     * @return void
     */
    public function __construct(
        string $name,
        UserProvider $provider,
        Session $session,
        Request $request = null
    ) {
        $this->name     = $name;
        $this->provider = $provider;
        $this->session  = $session;
        $this->request  = $request;
    }

    /**
     * Get the currently authenticated user.
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        if ( ! is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        if ( ! is_null($id) && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }
        
        if (is_null($this->user) && ! is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);
            
            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());
                
                $this->fireLoginEvent($this->user, true);
            }
        }
        
        return $this->user;
    }

    /**
     * Get the decrypted recaller cookie for the request.
     * 
     * @return \Syscodes\Components\Auth\Recaller|null
     */
    protected function recaller()
    {
        if (is_null($this->request)) {
            return;
        }
        
        if ($recaller = $this->request->cookies->get($this->getRecallerName())) {
            return new Recaller($recaller);
        }
    }

    /**
     * Pull a user from the repository by its "remember me" cookie token.
     * 
     * @param  \Syscodes\Components\Auth\Recaller  $recaller
     * 
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        if ( ! $recaller->valid() || $this->recallAttempted) {
            return;
        }
        
        $this->recallAttempted = true;
        
        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token()
        ));
        
        return $user;
    }

    /**
     * Get the ID for the currently authenticated user.
     * 
     * @return int|string|null
     */
    public function id()
    {
        if ($this->loggedOut) {
            return;
        }

        return $this->user()
                    ? $this->user()->getAuthIdentifier()
                    : $this->session->get($this->getName());
    }

    /**
     * Validate a user's credentials.
     * 
     * @param  array  $credentials
     * 
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        
        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     * 
     * @param  string  $field
     * @param  array  $extraConditions
     * 
     * @return \Syscodes\Components\Http\Response|null
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\UnauthorizedHttpException 
     */
    public function basic(string $field = 'email', array $extraConditions = [])
    {
        if ($this->check()) {
            return;
        }

        if ($this->attemptBasic($this->getRequest(), $field, $extraConditions)) {
            return;
        }

        return $this->failedBasicResponse();
    }

    /**
     * Perform a stateless HTTP Basic login attempt.
     * 
     * @param  string  $field
     * @param  array  $extraConditions
     * 
     * @return \Syscodes\Components\Http\Response|null
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\UnauthorizedHttpException
     */
    public function onceBasic(string $field = 'email', array $extraConditions = [])
    {
        $credentials = $this->basicCredentials($this->getRequest(), $field);
        
        if ( ! $this->once(array_merge($credentials, $extraConditions))) {
            return $this->failedBasicResponse();
        }
    }
    
    /**
     * Attempt to authenticate using basic authentication.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  string  $field
     * @param  array  $extraConditions
     * 
     * @return bool
     */
    protected function attemptBasic(Request $request, $field, $extraConditions = []): bool
    {
        if ( ! $request->getUser()) {
            return false;
        }
        
        return $this->attempt(array_merge(
            $this->basicCredentials($request, $field), $extraConditions
        ));
    }
    
    /**
     * Get the credential array for an HTTP Basic request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  string  $field
     * 
     * @return array
     */
    protected function basicCredentials(Request $request, $field): array
    {
        return [$field => $request->getUser(), 'password' => $request->getPassword()];
    }
    
    /**
     * Get the response for basic authentication.
     * 
     * @return void
     * 
     * @throws \Syscodes\Components\Core\Http\Exceptions\UnauthorizedHttpException
     */
    protected function failedBasicResponse(): void
    {
        throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     * 
     * @param  array  $credentials
     * @param  bool  $remember
     * 
     * @return bool
     */
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        $this->fireFailedEvent($user, $credentials);

        return false;
    }

    /**
     * Log a user into the application without sessions or cookies.
     * 
     * @param  array  $credentials
     * 
     * @return bool
     */
    public function once(array $credentials = []): bool
    {
        $this->fireAttemptEvent($credentials);
        
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);
            
            return true;
        }
        
        return false;
    }

    /**
     * Log a user into the application.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * 
     * @return void
     */
    public function login(Authenticatable $user, bool $remember = false): void
    {
        $this->updateSession($user->getAuthIdentifier());
        
        if ($remember) {
            $this->createRememberTokenIfDoesntExist($user);
            
            $this->queueRecallerCookie($user);
        }

        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }
    
    /**
     * Update the session with the given ID.
     * 
     * @param  string  $id
     * 
     * @return void
     */
    protected function updateSession(string $id): void
    {
        $this->session->put($this->getName(), $id);
        
        $this->session->migrate(true);
    }
    
    /**
     * Create a new remember token for the user if one doesn't already exist.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return void
     */
    protected function createRememberTokenIfDoesntExist(Authenticatable $user): void
    {
        $rememberToken = $user->getRememberToken();
        
        if (empty($rememberToken)) {
            $this->refreshRememberToken($user);
        }
    }
    
    /**
     * Refresh the "remember me" token for the user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return void
     */
    protected function refreshRememberToken(Authenticatable $user): void
    {
        $user->setRememberToken($token = Str::random(60));
        
        $this->provider->updateRememberToken($user, $token);
    }
    
    /**
     * Queue the recaller cookie into the cookie.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return void
     */
    protected function queueRecallerCookie(Authenticatable $user): void
    {
        $this->getCookie()->queue($this->createRecaller(
            $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword()
        ));
    }
    
    /**
     * Create a "remember me" cookie for a given ID.
     * 
     * @param  string  $value
     * 
     * @return \Syscodes\Components\Http\Cookie
     */
    protected function createRecaller(string $value)
    {
        return $this->getCookie()->make($this->getRecallerName(), $value, $this->getRememberDuration());
    }

    /**
     * Log the given user ID into the application.
     * 
     * @param  mixed  $id
     * @param  bool  $remember
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|bool
     */
    public function loginUsingId(mixed $id, bool $remember = false)
    {
        if ( ! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);
            
            return $user;
        }
        
        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     * 
     * @param  mixed  $id
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|bool
     */
    public function onceUsingId(mixed $id): bool
    {
        $user = $this->provider->retrieveById($id);
        
        $this->setUser($user);
        
        return ($user instanceof Authenticatable);
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     * 
     * @return bool
     */
    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }

    /**
     * Log the user out of the application.
     * 
     * @return void
     */
    public function logout(): void
    {
        $user = $this->user();
        
        $this->clearUserDataFromStorage();
        
        if ( ! is_null($this->user) && ! empty($user->getRememberToken())) {
            $this->refreshRememberToken($user);
        }
        
        if (isset($this->events)) {
            $this->events->dispatch(new Logout($this->name, $user));
        }
        
        $this->user = null;
        
        $this->loggedOut = true;
    }
    
    /**
     * Remove the user data from the session and cookies.
     * 
     * @return void
     */
    protected function clearUserDataFromStorage(): void
    {
        $this->session->remove($this->getName());
        
        if ( ! is_null($this->recaller())) {
            $this->getCookie()->queue(
                $this->getCookie()->erase($this->getRecallerName())
            );
        }
    }
    
    /**
     * Determine if the user matches the credentials.
     * 
     * @param  mixed  $user
     * @param  array  $credentials
     * 
     * @return bool
     */
    protected function hasValidCredentials(mixed $user, array $credentials): bool
    {
        return ! is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }
    
    /**
     * Register an authentication attempt event listener.
     * 
     * @param  mixed  $callback
     * 
     * @return void
     */
    public function attempting(mixed $callback): void
    {
        $this->events->listen(Events\Attempting::class, $callback) ?? null;
    }
    
    /**
     * Fire the attempt event with the arguments.
     * 
     * @param  array  $credentials
     * @param  bool  $remember
     * 
     * @return void
     */
    protected function fireAttemptEvent(array $credentials, bool $remember = false): void
    {
        $this->events->dispatch(new Attempting($this->name, $credentials, $remember)) ?? null;
    }
    
    /**
     * Fires the validated event if the dispatcher is set.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return void
     */
    protected function fireValidatedEvent(Authenticatable $user): void
    {
        $this->events->dispatch(new Validated($this->name, $user)) ?? null;
    }
    
    /**
     * Fire the login event if the dispatcher is set.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * 
     * @return void
     */
    protected function fireLoginEvent(Authenticatable $user, bool $remember = false): void
    {
        $this->events->dispatch(new Login($this->name, $user, $remember)) ?? null;
    }
    
    /**
     * Fire the authenticated event if the dispatcher is set.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return void
     */
    protected function fireAuthenticatedEvent(Authenticatable $user): void
    {
        $this->events->dispatch(new Authenticated($this->name, $user)) ?? null;
    }
    
    /**
     * Fire the other device logout event if the dispatcher is set.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return void
     */
    protected function fireOtherDeviceLogoutEvent(Authenticatable $user): void
    {
        $this->events->dispatch(new OtherDeviceLogout($this->name, $user)) ?? null;
    }
    
    /**
     * Fire the failed authentication attempt event with the given arguments.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * 
     * @return void
     */
    protected function fireFailedEvent(Authenticatable $user, array $credentials): void
    {
        $this->events->dispatch(new Failed($this->name, $user, $credentials)) ?? null;
    }
    
    /**
     * Get the last user we attempted to authenticate.
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable
     */
    public function getLastAttempted()
    {
        return $this->lastAttempted;
    }
    
    /**
     * Get a unique identifier for the auth session value.
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'login_'.$this->name.'_'.sha1(static::class);
    }
    
    /**
     * Get the name of the cookie used to store the "recaller".
     * 
     * @return string
     */
    public function getRecallerName(): string
    {
        return 'remember_'.$this->name.'_'.sha1(static::class);
    }
    
    /**
     * Get the number of minutes the remember me cookie should be valid for.
     * 
     * @return int
     */
    protected function getRememberDuration(): int
    {
        return $this->rememberDuration;
    }
    
    /**
     * Set the number of minutes the remember me cookie should be valid for.
     * 
     * @param  int  $minutes
     * 
     * @return static
     */
    public function setRememberDuration(int $minutes): static
    {
        $this->rememberDuration = $minutes;
        
        return $this;
    }
    
    /**
     * Get the cookie creator instance used by the guard.
     * 
     * @return \Syscodes\Components\Contracts\Cookie\QueueingFactory
     * 
     * @throws \RuntimeException
     */
    public function getCookie()
    {
        if ( ! isset($this->cookie)) {
            throw new RuntimeException('Cookie has not been set.');
        }
        
        return $this->cookie;
    }
    
    /**
     * Set the cookie creator instance used by the guard.
     * 
     * @param  \Syscodes\Components\Contracts\Cookie\QueueingFactory  $cookie
     * 
     * @return void
     */
    public function setCookie(Cookie $cookie): void
    {
        $this->cookie = $cookie;
    }
    
    /**
     * Get the event dispatcher instance.
     * 
     * @return \Syscodes\Components\Contracts\Events\Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->events;
    }
    
    /**
     * Set the event dispatcher instance.
     * 
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $events
     * 
     * @return void
     */
    public function setDispatcher(Dispatcher $events): void
    {
        $this->events = $events;
    }
    
    /**
     * Get the session store used by the guard.
     * 
     * @return \Syscodes\Components\Contracts\Session\Session
     */
    public function getSession(): session
    {
        return $this->session;
    }
    
    /**
     * Return the currently cached user.
     * 
     * @return \Syscodes\Components\Contracts\Auth\Authenticatable|null
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set the current user.
     * 
     * @param  \Syscodes\Components\Contracts\Auth\Authenticatable  $user
     * 
     * @return static
     */
    public function setUser(Authenticatable $user): static
    {
        $this->user      = $user;        
        $this->loggedOut = false;
        
        $this->fireAuthenticatedEvent($user);
        
        return $this;
    }
    
    /**
     * Get the current request instance.
     * 
     * @return \Syscodes\Components\Http\Request
     */
    public function getRequest(): request
    {
        return $this->request ?: Request::createFromRequestGlobals();
    }
    
    /**
     * Set the current request instance.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return static
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;
        
        return $this;
    }
}