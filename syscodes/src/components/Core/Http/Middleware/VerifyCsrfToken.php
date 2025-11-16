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

namespace Syscodes\Components\Core\Http\Middleware;

use Closure;
use Syscodes\Components\Cookie\CookieValue;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Contracts\Encryption\Encrypter;
use Syscodes\Components\Cookie\Middleware\EncryptCookies;
use Syscodes\Components\Encryption\Exceptions\DecryptException;
use Syscodes\Components\Session\Exceptions\TokenMismatchException;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Checks if exists a the CSRF token in the cookie.
 */
class VerifyCsrfToken
{
    use InteractsWithTime;

    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     * 
     * @var bool $addHttpCookie
     */
    protected $addHttpCookie = true;
    
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Components\Core\Application $app
     */
    protected $app;
    
    /**
     * The encrypter implementation.
     * 
     * @var \Syscodes\Components\Encryption\Encrypter $encrypter
     */
    protected $encrypter;
    
    /**
     * The URIs that should be excluded from CSRF verification.
     * 
     * @var array $except
     */
    protected $except = [];

    /**
     * Constructor. Create a new VerifyCsrftoken class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * @param  \Syscodes\Components\Contracts\Encryption\Encrypter  $encrypter
     * 
     * @return void
     */
    public function __construct(Application $app, Encrypter $encrypter)
    {
        $this->app       = $app;
        $this->encrypter = $encrypter;
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, Closure $next)
    {
        if ($this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)) {
            return take($next($request), function ($response) use ($request) {
                if ($this->addXsrfTokenCookie()) {
                    $this->addCookieToResponse($request, $response);
                }
            });
        }
        
        throw new TokenMismatchException('CSRF token mismatch');
    }
    
    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return bool
     */
    protected function isReading($request): bool
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Determine if the application is running unit tests.
     * 
     * @return bool
     */
    protected function runningUnitTests(): bool
    {
        return $this->app->runningInConsole() && $this->app->isUnitTests();
    }
    
    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return bool
     */
    protected function inExceptArray($request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            
            if ($request->is($except)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Determine if the session and input CSRF tokens match.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return bool
     */
    protected function tokensMatch($request): bool
    {
        $token = $this->getTokenFromRequest($request);
        
        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }
    
    /**
     * Get the CSRF token from the request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return string|null
     */
    protected function getTokenFromRequest($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        
        if ( ! $token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = CookieValue::remove($this->encrypter->decrypt($header, static::serialized()));
            } catch (DecryptException $e) {
                $token = '';
            }
        }
        
        return $token;
    }

    /**
     * Check if the cookie should be added to the response.
     * 
     * @return bool
     */
    public function addXsrfTokenCookie(): bool
    {
        return $this->addHttpCookie;
    }
    
    /**
     * Add the CSRF token to the response cookies.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addCookieToResponse($request, $response)
    {
        $config = config('session');
        
        $response->headers->setCookie($this->newCookie($request, $config));
        
        return $response;
    }

    /**
     * Create a new "XSRF-TOKEN" cookie that contains the CSRF token.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  array  $config
     * 
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function newCookie($request, $config)
    {
        return new Cookie(
            'XSRF-TOKEN',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false      
        );
    }

    /**
     * Determine if the cookie contents should be serialized.
     *
     * @return bool
     */
    public static function serialized(): bool
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }
}