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

namespace Syscodes\Components\Contracts\Events;

use Closure;

/**
 * For register events and can calls all your listeners.
 */
interface Dispatcher
{
    /**
     * Register an event listener with the dispatcher.
     * 
     * @param  string|array  $events
     * @param  \Closure|string|null  $listener
     * 
     * @return void
     */
    public function listen($events, $listener = null): void;

    /**
     * Register an event listener with the dispatcher.
     * 
     * @param  \Closure|string  $listener
     * @param  bool  $wildcard  
     * 
     * @return \Closure
     */
    public function makeListener($listener, $wildcard = false): Closure;

    /**
     * Determine if a given event has listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasListeners($eventName): bool;

    /**
     * Determine if the given event has any wildcard listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasWildcardListeners($eventName): bool;

    /**
     * Register an event subscriber with the dispatcher.
     * 
     * @param  object|string  $subscriber
     * 
     * @return void
     */
    public function subscribe($subscriber): void;

    /**
     * Resolve the subscriber instance.
     * 
     * @param  object|string  $subscriber
     * 
     * @return mixed
     */
    public function resolveSubscriber($subscriber);
    
    /**
     * Fire an event until the first non-null response is returned.
     * 
     * @param  string|object  $event
     * @param  mixed  $payload
     * 
     * @return array|null
     */
    public function until($event, $payload = []);

    /**
     * Dispatch an event and call the listeners.
     * 
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * 
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false);

    /**
     * Get all of the listeners for a given event name.
     * 
     * @param  string  $eventName
     * 
     * @return array
     */
    public function getListeners($eventName): array;

    /**
     * Remove a set of listeners from the dispatcher.
     * 
     * @param  string  $event
     * 
     * @return void
     */
    public function delete($event): void;
}