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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Events;

/**
 * For register events and can calls all your listeners.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
    public function listen($events, $listener = null);

    /**
     * Determine if a given event has listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasListeners($eventName);

    /**
     * Determine if the given event has any wildcard listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasWildcardListeners($eventName);

    /**
     * Register an event subscriber with the dispatcher.
     * 
     * @param  object|string  $subscriber
     * 
     * @return void
     */
    public function subscribe($subscriber);

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
    public function delete($event);
}