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

namespace Syscode\Events;

use Syscode\Container\Container;

/**
 * Dispatches events to registered listeners.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Dispatcher
{
    /**
     * The registered IoC container instance.
     * 
     * @var \Syscode\Container\Container $container
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
     * @param  \Syscode\Container\Container|null  $container  (null by default)
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
                $this->listeners[$vent][$priority][] = $this->makeListener($listener);

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
}