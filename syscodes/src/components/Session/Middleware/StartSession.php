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

namespace Syscodes\Components\Session\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Syscodes\Components\Contracts\Session\Session;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Routing\Route;
use Syscodes\Components\Session\SessionManager;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Support\Facades\Date;

/**
 * The start session allows authenticate logged on users.
 */
class StartSession
{
    /**
     * The session manager.
     * 
     * @var \Syscodes\Components\Session\SessionManager
     */
    protected $manager;

    /**
     * Constrcutor. Create a new StartSession class instance.
     * 
     * @param  \Syscodes\Components\Session\SessionManager  $manager
     * 
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;        
    }

    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next): Response
    {
        if ( ! $this->sessionConfigured()) {
            return $next($request);
        }
        
        $session = $this->getSession($request);

        return $this->handleStatefulRequest($request, $session, $next);
    }

    /**
     * Get the session implementation from the manager.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Contracts\Session\Session
     */
    public function getSession(Request $request)
    {
        return take($this->manager->driver(), function ($session) use ($request) {
            $session->setId($request->cookies->get($session->getName()));
        });
    }
    
    /**
     * Handle the given request within session state.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * @param  \Closure  $next
     * 
     * @return mixed
     */
    protected function handleStatefulRequest(Request $request, Session $session, Closure $next)
    {
        $request->setLenevorSession(
            $this->startSession($request, $session)
        );

        $this->collectGarbage($session);
        
        $response = $next($request);
        
        $this->storeCurrentUrl($request, $session);
        
        $this->addCookieToResponse($response, $session);
        
        $this->saveSession($request);
        
        return $response;
    }
    
    /**
     * Start the session for the given request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * 
     * @return \Syscodes\Components\Contracts\Session\Session
     */
    protected function startSession(Request $request, $session)
    {
        return take($session, function ($session) use ($request) {
            $session->setRequestOnHandler($request);
            
            $session->start();
        });
    }

    /**
     * Determine if a session driver has been configured.
     * 
     * @return bool
     */
    protected function sessionConfigured(): bool
    {
        return ! is_null($this->manager->getSessionConfig()['driver'] ?? null);
    }
    
    /**
     * Remove the garbage from the session if necessary.
     * 
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * 
     * @return void
     */
    protected function collectGarbage(Session $session): void
    {
        $config = $this->manager->getSessionConfig();

        if ($this->configHitsLottery($config)) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     * 
     * @param  array  $config
     * 
     * @return bool
     */
    protected function configHitsLottery(array $config): bool
    {
        return random_int(1, $config['lottery'][1]) <= $config['lottery'][0];
    }
    
    /**
     * Store the current URL for the request if necessary.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * 
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session): void
    {
        if ($request->isMethod('GET') &&
            $request->route() instanceof Route &&
            ! $request->ajax() &&
            ! $request->prefetch() &&
            ! $request->isPrecognitive()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }
    
    /**
     * Add the session cookie to the application response.
     * 
     * @param  \Symfony\Component\HttpFoundation\Response  $response 
     * @param  \Syscodes\Components\Contracts\Session\Session  $session
     * 
     * @return void
     */
    protected function addCookieToResponse(Response $response, Session $session): void
    {
        if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
            $response->headers->setCookie(
                new Cookie(
                    $session->getName(), 
                    $session->getId(), 
                    $this->getCookieExpirationDate(),
                    $config['path'], 
                    $config['domain'], 
                    $config['secure'] ?? false,
                    $config['httpOnly'] ?? true, 
                    false, 
                    $config['sameSite'] ?? null
                )
            );
        }
    }
    
    /**
     * Save the session data to storage.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return void
     */
    protected function saveSession($request): void
    {
        if ( ! $request->isPrecognitive()) {
            $this->manager->driver()->save();
        }
    }

    /**
     * Get the session lifetime in seconds.
     * 
     * @return int
     */
    protected function getSessionLifetimeInSeconds(): int
    {
        return ($this->manager->getSessionConfig()['lifetime'] ?? null) * 60;
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return \DateTimeInterface|int
     */
    protected function getCookieExpirationDate()
    {
        $config = $this->manager->getSessionConfig();

        return $config['expireOnClose'] ? 0 : Date::instance(
            Chronos::now()->addMinutes($config['lifetime'])
        );
    }
    
    /**
     * Determine if the configured session driver is persistent.
     * 
     * @param  array|null  $config
     * 
     * @return bool
     */
    protected function sessionIsPersistent(?array $config = null): bool
    {
        $config = $config ?: $this->manager->getSessionConfig();
        
        return ! is_null($config['driver'] ?? null);
    }
}