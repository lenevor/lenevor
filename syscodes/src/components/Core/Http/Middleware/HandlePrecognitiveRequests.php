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

use Syscodes\Components\Container\Container;
use Syscodes\Components\Core\Routing\PrecognitionCallableDispatcher;
use Syscodes\Components\Core\Routing\PrecognitionControllerDispatcher;
use Syscodes\Components\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Syscodes\Components\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;

/**
 * Allows the handle a precognitive request.
 */
class HandlePrecognitiveRequests
{
    /**
     * The container instance.
     *
     * @var \Syscodes\Components\Container\Container
     */
    protected $container;

    /**
     * Constructor. Create a new middleware instance.
     *
     * @param  \Syscodes\Components\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure  $next
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, $next)
    {
        if ( ! $request->isAttemptingPrecognition()) {
            return $this->appendVaryHeader($request, $next($request));
        }

        $bindings = $this->container->getBindings();
        $callableBinding = $bindings[CallableDispatcherContract::class] ?? null;
        $controllerBinding = $bindings[ControllerDispatcherContract::class] ?? null;

        $this->prepareForPrecognition($request);

        return take($next($request), function ($response) use ($request, $callableBinding, $controllerBinding) {
            $response->headers->set('Precognition', 'true');

            $this->appendVaryHeader($request, $response);

            $this->restoreDispatchers($callableBinding, $controllerBinding);
        });
    }

    /**
     * Prepare to handle a precognitive request.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @return void
     */
    protected function prepareForPrecognition($request)
    {
        $request->attributes->set('precognitive', true);

        $this->container->bind(CallableDispatcherContract::class, fn ($app) => new PrecognitionCallableDispatcher($app));
        $this->container->bind(ControllerDispatcherContract::class, fn ($app) => new PrecognitionControllerDispatcher($app));
    }

    /**
     * Append the appropriate "Vary" header to the given response.
     *
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Syscodes\Components\Http\Response  $response
     * @return \Syscodes\Components\Http\Response
     */
    protected function appendVaryHeader($request, $response)
    {
        return take($response, fn () => $response->headers->set('Vary', implode(', ', array_filter([
            $response->headers->get('Vary'),
            'Precognition',
        ]))));
    }

    /**
     * Restore the original route dispatcher bindings.
     *
     * @param  array|null  $callableBinding
     * @param  array|null  $controllerBinding
     * @return void
     */
    protected function restoreDispatchers($callableBinding, $controllerBinding)
    {
        if ($callableBinding) {
            $this->container->bind(CallableDispatcherContract::class, $callableBinding['value'], $callableBinding['shared']);
        }

        if ($controllerBinding) {
            $this->container->bind(ControllerDispatcherContract::class, $controllerBinding['value'], $controllerBinding['shared']);
        }
    }
}