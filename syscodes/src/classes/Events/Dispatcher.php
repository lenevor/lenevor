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

namespace Syscodes\Events;

use Syscodes\Support\Arr;
use Syscodes\Support\Str;
use Syscodes\Contracts\Container\Container;
use Syscodes\Contracts\Events\Dispatcher as DispatcherContract;

/**
 * Dispatches events to registered listeners.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Dispatcher implements DispatcherContract
{
    /**
     * The registered IoC container instance.
     * 
     * @var \Syscodes\Contracts\Container\Container $container
     */
    protected $container;

    /**
     * The event dispatching.
     * 
     * @var array $dispatching
     */
    protected $dispatching = [];

    /**
     * The registered event listeners.
     * 
     * @var array $listeners
     */
    protected $listeners = [];

    /**
     * The sorted event listeners.
     * 
     * @var array $sorted
     */
    protected $sorted = [];

    /**
     * The wilcards listeners.
     * 
     * @var array $wilcards
     */
    protected $wilcards = [];

    /**
     * Constructor. Create a new event distpacher instance.
     * 
     * @param  \Syscodes\Contracts\Container\Container|null  $container  (null by default)
     * 
     * @return void
     */
    public function __construct(Container $container = null)
    {
        $this->container = $container ?: new Container;
    }

    /**
     * Register an event listener with the dispatcher.
     * 
     * @param  string|array  $events
     * @param  \Closure|string  $listener
     * @param  int  $priority  (0 by default)
     * 
     * @return void
     */
    public function listen($events, $listener, $priority = 0)
    {
        foreach ((array) $events as $event)
        {
            if (Str::contains($event, '*'))
            {
                $this->setupWilcardListen($event, $listener);
            }
            else
            {
                $this->listeners[$event][$priority][] = $this->makeListener($listener);

                $this->clearSortedListeners($event);
            }
        }
    }

    /**
     * Setup a wildcard listener callback.
     * 
     * @param  string|array  $event
     * @param  \Closure|string  $listener
     * 
     * @return void
     */
    protected function setupWilcardListen($event, $listener)
    {
        $this->wilcards[$event][] = $this->makeListener($listener, true);
    }

    /**
     * Register an event listener with the dispatcher.
     * 
     * @param  \Closure|string  $listener
     * @param  bool  $wilcard  (false by default)
     * 
     * @return \Closure
     */
    public function makeListener($listener, $wilcard = false)
    {
        if (is_string($listener))
        {
            return $this->createClassListener($listener, $wilcard);
        }

        return function ($event, $payload) use ($listener, $wilcard) {

            if ($wilcard)
            {
                return $listener($event, $payload);
            }
            
            return $listener(...array_values($payload));

        };
    }

    /**
     * Create a class based listener using the IoC container.
     * 
     * @param  string  $listener
     * @param  bool  $wilcard  (false by default)
     * 
     * @return \Closure
     */
    public function createClassListener($listener, $wilcard = false)
    {
        return function ($event, $payload) use ($listener, $wilcard) {

            if ($wilcard)
            {
                return call_user_func($this->createClassClosure($listener), $event, $payload);
            }

            return call_user_func_array($this->createClassClosure($listener), $payload);
            
        };
    }

    /**
     * Create the class based event callable.
     * 
     * @param  string  $listener
     * 
     * @return \Callable
     */
    protected function createClassClosure($listener)
    {
        list($class, $method) = $this->parseClassCallback($listener);

        $instance = $this->container->make($class);

        return [$instance, $method];
    }

    /**
     * Parse the class listener into class and method.
     * 
     * @param  string  $listener
     * 
     * @return array
     */
    protected function parseClassCallback($listener)
    {
        return Str::parseCallback($listener, 'handle');
    }

    /**
     * Clear the sorted listeners for an event.
     * 
     * @param  string  $event
     * 
     * @return void
     */
    protected function clearSortedListeners($event)
    {
        unset($this->sorted[$event]);
    }

    /**
     * Determine if a given event has listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) || 
               isset($this->wilcards[$eventName]) ||
               $this->hasWilcardListeners($eventName);
    }

    /**
     * Determine if the given event has any wildcard listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasWilcardListeners($eventName)
    {
        foreach ($this->wilcards as $key => $listeners)
        {
            if (Str::is($key, $eventName))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Register an event subscriber with the dispatcher.
     * 
     * @param  object|string  $subscriber
     * 
     * @return void
     */
    public function subscribe($subscriber)
    {
        $subscriber = $this->resolveSubscriber($subscriber);

        $subscriber->subscribe($this);
    }

    /**
     * Resolve the subscriber instance.
     * 
     * @param  object|string  $subscriber
     * 
     * @return mixed
     */
    public function resolveSubscriber($subscriber)
    {
        if (is_string($subscriber))
        {
            return $this->container->make($subscriber);
        }

        return $subscriber;
    }

    /**
     * Dispatch an event and call the listeners.
     * 
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt  (false by default)
     * 
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        $responses = [];
        
        list($event, $payload) = $this->parseEventPayload($event, $payload);

        $this->dispatching[] = $event;

        foreach ($this->getListeners($event) as $listener)
        {
            $response = $listener($event, $payload);

            // If the listener returns a response and it is verified that the halting 
            // of the event is enabled, only this response will be returned, and the 
            // rest of the listeners are not called. Otherwise, the response is added 
            // in the response list.
            if ($halt && ! is_null($response))
            {
                array_pop($this->dispatching);

                return $response;
            }

            // If a boolean false is returned from a listener, the event is stopped 
            // spreading to other listeners in the chain, otherwise we will continue 
            // touring the listeners and dispatching everyone in our sequence.
            if ($response === false)
            {
                break;
            }

            $responses[] = $response;
        }

        array_pop($this->dispatching);

        return $halt ? null : $responses;
    }

    /**
     * Parse the given event and payload and prepare them for dispatching.
     * 
     * @param  mixed  $event
     * @param  mixed  $payload
     * 
     * @return array
     */
    protected function parseEventPayload($event, $payload)
    {
        if (is_object($event))
        {
            list($payload, $event) = [[$event], getClass($event, false)];
        }
        elseif ( ! is_array($payload))
        {
            $payload = [$payload];
        }

        return [$event, Arr::wrap($payload)];
    }

    /**
     * Get all of the listeners for a given event name.
     * 
     * @param  string  $eventName
     * 
     * @return array
     */
    public function getListeners($eventName)
    {
        $wilcards = $this->getWilcardListeners($eventName);

        if ( ! isset($this->sorted[$eventName]))
        {
            $this->sortListeners($eventName);
        }

        return array_merge($this->sorted[$eventName], $wilcards);
    }

    /**
     * Get the wildcard listeners for the event.
     * 
     * @param  string  $eventName
     * 
     * @return array
     */
    protected function getWilcardListeners($eventName)
    {
        $wilcards = [];

        foreach ($this->wilcards as $key => $listeners)
        {
            if (Str::is($key, $eventName))
            {
                $wilcards = array_merge($wilcards, $listeners);
            }
        }

        return $wilcards;
    }

    /**
     * Sort the listeners for a given event by priority.
     * 
     * @param  string  $eventName
     * 
     * @return array
     */
    protected function sortListeners($eventName)
    {
        $this->sorted[$eventName] = [];
        
        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off and
        // sorted event listeners so we do not have to re-sort on every events.
        if (isset($this->listeners[$eventName]))
        {
            krsort($this->listeners[$eventName]);

            $this->sorted[$eventName] = call_user_func_array(
                'array_merge', $this->listeners[$eventName]
            );
        }
    }

    /**
     * Remove a set of listeners from the dispatcher.
     * 
     * @param  string  $event
     * 
     * @return void
     */
    public function delete($event)
    {
        if (Str::contains($event, '*'))
        {
            unset($this->wilcards[$event]);
        }
        else
        {
            unset($this->listeners[$event], $this->sorted[$event]);
        }
    }
}