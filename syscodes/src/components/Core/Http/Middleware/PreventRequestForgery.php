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

namespace Syscodes\Components\Core\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Syscodes\Components\Cookie\CookieValue;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Contracts\Encryption\Encrypter;
use Syscodes\Components\Cookie\Middleware\EncryptCookies;
use Syscodes\Components\Core\Http\Middleware\Concerns\ExcludesPaths;
use Syscodes\Components\Encryption\Exceptions\DecryptException;
use Syscodes\Components\Http\Exceptions\OriginMismatchException;
use Syscodes\Components\Session\Exceptions\TokenMismatchException;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Support\Arr;

/**
 * Checks if exists a the CSRF token in the cookie.
 */
class PreventRequestForgery
{
    use ExcludesPaths,
        InteractsWithTime;

    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     * 
     * @var bool
     */
    protected $addHttpCookie = true;
    
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application
     */
    protected $app;
    
    /**
     * The encrypter implementation.
     * 
     * @var \Syscodes\Components\Encryption\Encrypter
     */
    protected $encrypter;
    
    /**
     * The URIs that should be excluded from CSRF verification.
     * 
     * @var array<int, string>
     */
    protected $except = [];

    /**
     * Indicates whether requests from the same site should be allowed.
     *
     * @var bool
     */
    protected static $allowSameSite = false;

    /**
     * The globally ignored URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected static $neverVerify = [];

    /**
     * Indicates whether only origin verification should be used.
     *
     * @var bool
     */
    protected static $originOnly = false;

    /**
     * Constructor. Create a new PreventRequestForgery class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * @param  \Syscodes\Components\Contracts\Encryption\Encrypter  $encrypter 
     * @return void
     */
    public function __construct(Application $app, Encrypter $encrypter)
    {
        $this->app = $app;
        $this->encrypter = $encrypter;
    }
    
    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Request): (\Syscodes\Components\Http\Response)  $next
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, Closure $next)
    {
        if ($this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->hasValidOrigin($request) ||
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
     * Determine if the request has a valid origin based on the Sec-Fetch-Site header.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @return bool
     *
     * @throws \Syscodes\Components\Http\Exceptions\OriginMismatchException
     */
    protected function hasValidOrigin($request)
    {
        $secFetchSite = $request->header('Sec-Fetch-Site');

        if ($secFetchSite === 'same-origin') {
            return true;
        }

        if ($secFetchSite === 'same-site' && static::$allowSameSite) {
            return true;
        }

        if (static::$originOnly) {
            throw new OriginMismatchException('Origin mismatch.');
        }

        return false;
    }    
    
    /**
     * Determine if the session and input CSRF tokens match.
     * 
     * @param  \Syscodes\Components\Http\Request  $request 
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
        if (static::$originOnly) {
            return false;
        }

        return $this->addHttpCookie;
    }
    
    /**
     * Add the CSRF token to the response cookies.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response 
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
     * Indicate that the given URIs should be excluded from CSRF verification.
     *
     * @param  array|string  $uris
     * @return void
     */
    public static function except($uris)
    {
        static::$neverVerify = array_values(array_unique(
            array_merge(static::$neverVerify, Arr::wrap($uris))
        ));
    }

    /**
     * Indicate that requests from the same site should be allowed.
     *
     * @param  bool  $allow
     * @return void
     */
    public static function allowSameSite($allow = true): void
    {
        static::$allowSameSite = $allow;
    }

    /**
     * Indicate that only origin verification should be used.
     *
     * @param  bool  $originOnly
     * @return void
     */
    public static function useOriginOnly($originOnly = true): void
    {
        static::$originOnly = $originOnly;
    }

    /**
     * Get the URIs that should be excluded.
     *
     * @return array
     */
    public function getExcludedPaths(): array
    {
        return array_merge($this->except, static::$neverVerify);
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

    /**
     * Flush the state of the middleware.
     *
     * @return void
     */
    public static function flushState(): void
    {
        static::$neverVerify = [];
        static::$allowSameSite = false;
        static::$originOnly = false;
    }
}