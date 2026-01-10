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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth\Middleware;

use Closure;
use Syscodes\Components\Support\Facades\Date;
use Syscodes\Components\Contracts\Routing\RouteResponse;
use Syscodes\Components\Contracts\Routing\UrlGenerator;

/**
 * 
 */
class RequirePassword
{
    /**
     * The response factory instance.
     *
     * @var \Syscodes\Components\Contracts\Routing\RouteResponse
     */
    protected $responseFactory;

    /**
     * The URL generator instance.
     *
     * @var \Syscodes\Components\Contracts\Routing\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * The password timeout.
     *
     * @var int
     */
    protected $passwordTimeout;

    /**
     * Constructor. Create a new middleware class instance.
     *
     * @param  \Syscodes\Components\Contracts\Routing\RouteResponse  $ResponseFactory
     * @param  \Syscodes\Components\Contracts\Routing\UrlGenerator  $urlGenerator
     * @param  int|null  $passwordTimeout
     * 
     * @return void
     */
    public function __construct(RouteResponse $responseFactory, UrlGenerator $urlGenerator, $passwordTimeout = null)
    {
        $this->responseFactory = $responseFactory;
        $this->urlGenerator = $urlGenerator;
        $this->passwordTimeout = $passwordTimeout ?: 10800;
    }

    /**
     * Specify the redirect route and timeout for the middleware.
     *
     * @param  string|null  $redirectToRoute
     * @param  string|int|null  $passwordTimeoutSeconds
     * 
     * @return string
     *
     * @named-arguments-supported
     */
    public static function using($redirectToRoute = null, $passwordTimeoutSeconds = null): string
    {
        return static::class.':'.implode(',', func_get_args());
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @param  string|int|null  $passwordTimeoutSeconds
     * 
     * @return mixed
     */
    public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null)
    {
        if ($this->shouldConfirmPassword($request, $passwordTimeoutSeconds)) {
            if ($request->expectsJson()) {
                return $this->responseFactory->json([
                    'message' => 'Password confirmation required.',
                ], 423);
            }

            return $this->responseFactory->redirectGuest(
                $this->urlGenerator->route($redirectToRoute ?: 'password.confirm')
            );
        }

        return $next($request);
    }

    /**
     * Determine if the confirmation timeout has expired.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  int|null  $passwordTimeoutSeconds
     * 
     * @return bool
     */
    protected function shouldConfirmPassword($request, $passwordTimeoutSeconds = null): bool
    {
        $confirmedAt = Date::now() - $request->session()->get('auth.password_confirmed_at', 0);

        return $confirmedAt > ($passwordTimeoutSeconds ?? $this->passwordTimeout);
    }
}