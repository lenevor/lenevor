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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.6.0
 */

namespace Syscodes\Contracts\Events;

/**
 * For register events and can calls all your listeners.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface Dispatcher
{
    /**
     * Register an event listener with the dispatcher.
     * 
     * @param  string|array  $events
     * @param  \Closure|string  $listener
     * @param  int  $priority  (0 by default)
     * 
     * @return void
     */
    public function listen($events, $listener, $priority = 0);

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
    public function hasWilcardListeners($eventName);

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
     * @param  bool  $halt  (false by default)
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