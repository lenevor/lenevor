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
use ErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Syscodes\Components\Contracts\Core\Application;
use Syscodes\Components\Core\Http\MaintenanceModeBypassCookie;
use Syscodes\Components\Core\Http\Middleware\Concerns\ExcludesPaths;
use Syscodes\Components\Support\Arr;
use TypeError;

/**
 * Checks the incoming request has a maintenance mode.
 */
class PreventRequestsMaintenance
{
    use ExcludesPaths;

    /**
     * The application implementation.
     *
     * @var \Syscodes\Components\Contracts\Core\Application
     */
    protected $app;

    /**
     * The URIs that should be excluded.
     *
     * @var array<int, string>
     */
    protected $except = [];

    /**
     * The URIs that should be accessible during maintenance.
     *
     * @var array
     */
    protected static $neverPrevent = [];

    /**
     * Constructor. Create a new middleware instance.
     *
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

     /**
     * Handle an incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \ErrorException
     */
    public function handle($request, Closure $next)
    {
        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        if ($this->app->maintenanceMode()->active()) {
            try {
                $data = $this->app->maintenanceMode()->data();
            } catch (ErrorException|TypeError $exception) {
                if (! $this->app->maintenanceMode()->active()) {
                    return $next($request);
                }

                throw $exception;
            }

            if (is_string($data['secret'] ?? null) && hash_equals($data['secret'], $request->path())) {
                return $this->bypassResponse($data['secret']);
            }

            if ($this->hasValidBypassCookie($request, $data)) {
                return $next($request);
            }

            if (isset($data['redirect']) && ! $request->expectsJson()) {
                $path = $data['redirect'] === '/'
                    ? $data['redirect']
                    : trim($data['redirect'], '/');

                if ($request->path() !== $path) {
                    return redirect($path);
                }
            }

            if (isset($data['template']) && ! $request->expectsJson()) {
                return response(
                    $data['template'],
                    $data['status'] ?? 503,
                    $this->getHeaders($data)
                );
            }

            throw new HttpException(
                $data['status'] ?? 503,
                'Service Unavailable',
                null,
                $this->getHeaders($data)
            );
        }

        return $next($request);
    }

    /**
     * Determine if the incoming request has a maintenance mode bypass cookie.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  array  $data
     * @return bool
     */
    protected function hasValidBypassCookie($request, array $data)
    {
        return isset($data['secret']) &&
            $request->cookie('lenevor_maintenance') &&
            MaintenanceModeBypassCookie::isValid(
                $request->cookie('lenevor_maintenance'),
                $data['secret']
            );
    }

    /**
     * Redirect the user to their intended destination with a maintenance mode bypass cookie.
     *
     * @param  string  $secret
     * @return \Syscodes\Components\Http\RedirectResponse
     */
    protected function bypassResponse(string $secret)
    {
        return redirect()->intended('/')->withCookie(
            MaintenanceModeBypassCookie::create($secret)
        );
    }

    /**
     * Get the headers that should be sent with the response.
     *
     * @param  array  $data
     * @return array
     */
    protected function getHeaders($data)
    {
        $headers = isset($data['retry']) ? ['Retry-After' => $data['retry']] : [];

        if (isset($data['refresh'])) {
            $headers['Refresh'] = $data['refresh'];
        }

        return $headers;
    }

    /**
     * Get the URIs that should be excluded.
     *
     * @return array
     */
    public function getExcludedPaths()
    {
        return array_merge($this->except, static::$neverPrevent);
    }

    /**
     * Indicate that the given URIs should always be accessible.
     *
     * @param  array|string  $uris
     * @return void
     */
    public static function except($uris)
    {
        static::$neverPrevent = array_values(array_unique(
            array_merge(static::$neverPrevent, Arr::wrap($uris))
        ));
    }

    /**
     * Flush the state of the middleware.
     *
     * @return void
     */
    public static function flushState()
    {
        static::$neverPrevent = [];
    }
}