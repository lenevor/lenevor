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

namespace Syscodes\Components\Events;

use Syscodes\Components\Contracts\Events\Dispatcher as DispatcherContract;
use Syscodes\Components\Support\Traits\ForwardsCalls;

/**
 * Dispatches null events.
 */
class NullDispatcher implements DispatcherContract
{
    use ForwardsCalls;

    /**
     * The underlying event dispatcher instance.
     *
     * @var \Syscodes\Components\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new event dispatcher instance that does not fire.
     *
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $dispatcher
     */
    public function __construct(DispatcherContract $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Don't fire an event.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * 
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        //
    }

    /**
     * Don't register an event and payload to be fired later.
     *
     * @param  string  $event
     * @param  array  $payload
     * 
     * @return void
     */
    public function push($event, $payload = []): void
    {
        //
    }

    /**
     * Don't dispatch an event.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * 
     * @return array|null
     */
    public function until($event, $payload = [])
    {
        //
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  \Closure|string|array  $events
     * @param  \Closure|string|array|null  $listener
     * 
     * @return void
     */
    public function listen($events, $listener = null): void
    {
        $this->dispatcher->listen($events, $listener);
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasListeners($eventName): bool
    {
        return $this->dispatcher->hasListeners($eventName);
    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  object|string  $subscriber
     * 
     * @return void
     */
    public function subscribe($subscriber): void
    {
        $this->dispatcher->subscribe($subscriber);
    }

    /**
     * Flush a set of pushed events.
     *
     * @param  string  $event
     * 
     * @return void
     */
    public function flush($event): void
    {
        $this->dispatcher->flush($event);
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * 
     * @return void
     */
    public function delete($event): void
    {
        $this->dispatcher->delete($event);
    }

    /**
     * Deleted all of the queued listeners.
     *
     * @return void
     */
    public function deletePushed(): void
    {
        $this->dispatcher->deletePushed();
    }

    /**
     * Method magic.
     * 
     * Dynamically pass method calls to the underlying dispatcher.
     *
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->dispatcher, $method, $parameters);
    }
}