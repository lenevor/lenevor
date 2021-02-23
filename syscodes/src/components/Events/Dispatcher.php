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

namespace Syscodes\Events;

use Syscodes\Support\Str;
use Syscodes\Collections\Arr;
use Syscodes\Contracts\Container\Container;
use Syscodes\Contracts\Events\Dispatcher as DispatcherContract;

/**
 * Dispatches events to registered listeners.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * The wilcards listeners.
     * 
     * @var array $wilcards
     */
    protected $wildcards = [];

    /**
     * The cached wildcard listeners.
     * 
     * @var array $wildcardsCache
     */
    protected $wildcardsCache = [];

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
     * @param  \Closure|string|null  $listener
     * 
     * @return void
     */
    public function listen($events, $listener = null)
    {
        foreach ((array) $events as $event) {
            if (Str::contains($event, '*')) {
                $this->setupWilcardListen($event, $listener);
            } else {
                $this->listeners[$event][] = $this->makeListener($listener);
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
        $this->wildcards[$event][] = $this->makeListener($listener, true);

        $this->wildcardsCache = [];
    }

    /**
     * Register an event listener with the dispatcher.
     * 
     * @param  \Closure|string  $listener
     * @param  bool  $wildcard  (false by default)
     * 
     * @return \Closure
     */
    public function makeListener($listener, $wildcard = false)
    {
        if (is_string($listener)) {
            return $this->createClassListener($listener, $wildcard);
        }
        
        if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
            return $this->createClassListener($listener, $wildcard);
        }

        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return $listener($event, $payload);
            }
            
            return $listener(...array_values($payload));
        };
    }

    /**
     * Create a class based listener using the IoC container.
     * 
     * @param  string  $listener
     * @param  bool  $wildcard  (false by default)
     * 
     * @return \Closure
     */
    public function createClassListener($listener, $wildcard = false)
    {
        return function ($event, $payload) use ($listener, $wildcard) {
            if ($wildcard) {
                return call_user_func($this->createClassClosure($listener), $event, $payload);
            }
            
            $callable = $this->createClassClosure($listener);
            
            return $callable(...array_values($payload));
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
        [$class, $method] = is_array($listener)
                          ? $listener
                          : $this->parseClassCallback($listener);
                                
        if ( ! method_exists($class, $method)) {
            $method = '__invoke';
        }

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
     * Determine if a given event has listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) || 
               isset($this->wildcards[$eventName]) ||
               $this->hasWilcardListeners($eventName);
    }

    /**
     * Determine if the given event has any wildcard listeners.
     * 
     * @param  string  $eventName
     * 
     * @return bool
     */
    public function hasWildcardListeners($eventName)
    {
        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
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

        $events = $subscriber->subscribe($this);
        
        if (is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $this->listen($event, $listener);
                }
            }
        }
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
        if (is_string($subscriber)) {
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
        [$event, $payload] = $this->parseEventPayload(
            $event, $payload
        );

        $this->dispatching[] = $event;

        $responses = [];
        
        foreach ($this->getListeners($event) as $listener) {
            $response = $listener($event, $payload);

            // If the listener returns a response and it is verified that the halting 
            // of the event is enabled, only this response will be returned, and the 
            // rest of the listeners are not called. Otherwise, the response is added 
            // in the response list.
            if ($halt && ! is_null($response)) {
                array_pop($this->dispatching);

                return $response;
            }

            // If a boolean false is returned from a listener, the event is stopped 
            // spreading to other listeners in the chain, otherwise we will continue 
            // touring the listeners and dispatching everyone in our sequence.
            if ($response === false) {
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
        if (is_object($event)) {
            [$payload, $event] = [[$event], getClass($event, true)];
        } elseif ( ! is_array($payload)) {
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
        $listeners = $this->listeners[$eventName] ?? [];

        $listeners = array_merge(
            $listeners,
            $this->wildcardsCache[$eventName] ?? $this->getWilcardListeners($eventName)
        );

        return class_exists($eventName, false) 
                    ? $this->addInterfaceListener($eventName, $listeners)
                    : $listeners;
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
        $wildcards = [];

        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
                $wildcards = array_merge($wildcards, $listeners);
            }
        }

        return $this->wildcardsCache[$eventName] = $wildcards;
    }

    /**
     * Add the listeners for the event's interfaces to the given array.
     * 
     * @param  string  $eventName
     * @param  array  $listeners
     * 
     * @return array
     */
    protected function addInterfaceListener($eventName, array $listeners = [])
    {
        foreach (class_implements($eventName) as $interface) {
            if (iseet($this->listeners[$interface])) {
                foreach ($this->listeners[$interface] as $names)
                {
                    $listeners = array_merge($listeners, (array) $names);
                }
            }
        }

        return $listeners;
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
        if (Str::contains($event, '*')) {
            unset($this->wildcards[$event]);
        } else {
            unset($this->listeners[$event]);
        }

        foreach ($this->wildcardsCache as $key => $listeners) {
            if (Str::is($event, $key)) {
                unset($this->wildcardsCache[$key]);
            }
        }
    }
}