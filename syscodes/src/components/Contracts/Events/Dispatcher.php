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
     * Determine if a given event has listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasListeners($eventName): bool;

    /**
     * Register an event subscriber with the dispatcher.
     * 
     * @param  object|string  $subscriber
     * 
     * @return void
     */
    public function subscribe($subscriber): void;
    
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
     * Register an event and payload to be fired later.
     *
     * @param  string  $event
     * @param  object|array  $payload
     * 
     * @return void
     */
    public function push($event, $payload = []): void;

    /**
     * Flush a set of pushed events.
     *
     * @param  string  $event
     * 
     * @return void
     */
    public function flush($event): void;

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
     * Remove a set of listeners from the dispatcher.
     * 
     * @param  string  $event
     * 
     * @return void
     */
    public function delete($event): void;

    /**
     * Deleted all of the pushed listeners.
     *
     * @return void
     */
    public function deletePushed(): void;
}