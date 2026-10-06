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

namespace Syscodes\Components\Database\Erostrine\Concerns;

use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Events\NullDispatcher;
use InvalidArgumentException;
use Syscodes\Components\Support\Arr;

/**
 * HasEvents.
 */
trait HasEvents
{
    /**
     * The event map for the model.
     * 
     * @var array
     */
    protected $dispatchEvents = [];
    
    /**
     * User exposed observable events.
     * 
     * @var array
     */
    protected $observables = [];
    
    /**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher
	 */
	protected static $dispatcher;

     /**
     * Register observers with the model.
     *
     * @param  object|string[]|string  $classes
     * @return void
     *
     * @throws \RuntimeException
     */
    public static function observe($classes)
    {
        $instance = new static;

        foreach (Arr::wrap($classes) as $class) {
            $instance->registerObserver($class);
        }
    }

    /**
     * Register a single observer with the model.
     *
     * @param  object|string  $class
     * @return void
     *
     * @throws \RuntimeException
     */
    protected function registerObserver($class)
    {
        $className = $this->resolveObserverClassName($class);

        // When registering a model observer, we will spin through the possible events
        // and determine if this observer has that method.
        foreach ($this->getObservableEvents() as $event) {
            if (method_exists($class, $event)) {
                static::registerModelEvent($event, $className.'@'.$event);
            }
        }
    }

    /**
     * Resolve the observer's class name from an object or string.
     *
     * @param  object|string  $class
     * @return class-string
     *
     * @throws \InvalidArgumentException
     */
    private function resolveObserverClassName($class)
    {
        if (is_object($class)) {
            return get_class($class);
        }

        if (class_exists($class)) {
            return $class;
        }

        throw new InvalidArgumentException('Unable to find observer: '.$class);
    }

    /**
     * Get the observable event names.
     * 
     * @return array
     */
    public function getObservableEvents(): array
    {
        return array_merge(
            [
                'retrieved', 'creating', 'created', 'updating', 'updated',
                'saving', 'saved', 'restoring', 'restored', 'replicating',
                'trashed', 'deleting', 'deleted'
            ],
            $this->observables
        );
    }

    /**
     * Set the observable event names.
     * 
     * @param  string[]  $observables 
     * @return $this
     */
    public function setObservableEvents(array $observables): static
    {
        $this->observables = $observables;

        return $this;
    }

    /**
     * Add an observable event name.
     * 
     * @param  string|string[]  $observables 
     * @return void
     */
    public function addObservableEvents($observables): void
    {
        $observables = is_array($observables) ? $observables : func_get_args();

        $this->observables = array_unique(array_merge($this->observables, $observables));
    }

    /**
     * Remove an observable event name.
     * 
     * @param  string|string[]  $observables 
     * @return void
     */
    public function removeObservableEvents($observables): void
    {
        $observables = is_array($observables) ? $observables : func_get_args();

        $this->observables = array_diff($this->observables, $observables);
    }

    /**
     * Register a model event with the dispatcher.
     * 
     * @param  string  $event
     * @param  \Closure|string  $callback 
     * @return void
     */
    public static function registerModelEvent($event, $callback): void
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;

            static::$dispatcher->listen("erostrine.{$event}: {$name}", $callback);
        }
    }

    /**
     * Fire the given event for the model.
     * 
     * @param  string  $event
     * @param  bool  $detain 
     * @return mixed
     */
    public function fireModelEvent($event, bool $detain = true)
    {
        if ( ! isset(static::$dispatcher)) {
            return true;
        }
        
        // First, we will get the proper method to call on the event dispatcher, and then we
        // will attempt to fire a custom, object based event for the given event.
        $method = $detain ? 'until' : 'dispatch';

        $result = $this->filterModelEventResults(
            $this->fireCustomModelEvent($event, $method)
        );

        if ($result === false) {
            return false;
        }

        return ! empty($result) ? $result : static::$dispatcher->{$method}(
            "erostrine.{$event}: ".static::class, $this
        );
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @param  string  $event
     * @param  'until'|'dispatch'  $method
     * @return array|null|void
     */
    protected function fireCustomModelEvent($event, $method)
    {
        if ( ! isset($this->dispatchEvents[$event])) {
            return;
        }

        $result = static::$dispatcher->$method(new $this->dispatchEvents[$event]($this));

        if ( ! is_null($result)) {
            return $result;
        }
    }

    /**
     * Filter the model event results.
     *
     * @param  mixed  $result
     * @return mixed
     */
    protected function filterModelEventResults($result)
    {
        if (is_array($result)) {
            $result = array_filter($result, function ($response) {
                return ! is_null($response);
            });
        }

        return $result;
    }

    /**
     * Register a retrieved model event with the dispatcher.
     *
     * @param  callable|array|class-string  $callback
     * @return void
     */
    public static function retrieved($callback): void
    {
        static::registerModelEvent('retrieved', $callback);
    }

    /**
     * Register a saving model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function saving($callback): void
    {
        static::registerModelEvent('saving', $callback);
    }

    /**
     * Register a saved model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function saved($callback): void
    {
        static::registerModelEvent('saved', $callback);
    }

    /**
     * Register a updating model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function updating($callback): void
    {
        static::registerModelEvent('updating', $callback);
    }

    /**
     * Register a updated model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function updated($callback): void
    {
        static::registerModelEvent('updated', $callback);
    }

    /**
     * Register a creating model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function creating($callback): void
    {
        static::registerModelEvent('creating', $callback);
    }

    /**
     * Register a created model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function created($callback): void
    {
        static::registerModelEvent('created', $callback);
    }

    /**
     * Register a replicating model event with the dispatcher.
     *
     * @param  callable|array|class-string  $callback
     * @return void
     */
    public static function replicating($callback)
    {
        static::registerModelEvent('replicating', $callback);
    }

    /**
     * Register a deleting model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function deleting($callback): void
    {
        static::registerModelEvent('deleting', $callback);
    }

    /**
     * Register a deleted model event with the dispatcher.
     * 
     * @param  callable|array|class-string  $callback 
     * @return void
     */
    public static function deleted($callback): void
    {
        static::registerModelEvent('deleted', $callback);
    }
    
    /**
     * Remove all of the event listeners for the model.
     * 
     * @return void
     */
    public static function flushEventListeners()
    {
        if ( ! isset(static::$dispatcher)) {
            return;
        }

        $instance = new static;
        
        foreach ($instance->getObservableEvents() as $event) {
            static::$dispatcher->delete("erostrine.{$event}: ".static::class);
        }
        
        foreach ($instance->dispatchEvents as $event) {
            static::$dispatcher->delete($event);
        }
    }

    /**
     * Get the event map for the model.
     *
     * @return array<string, class-string>
     */
    public function dispatchEvents()
    {
        return $this->dispatchEvents;
    }

    /**
     * Get the event dispatcher instance.
     * 
     * @return \Syscodes\Components\Contracts\Events\Dispatcher
     */
    public static function getEventDispatcher()
    {
        return static::$dispatcher;
    }

    /**
     * Set the event dispatcher instance.
     * 
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher  $dispatcher 
     * @return void
     */
    public static function setEventDispatcher(Dispatcher $dispatcher): void
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Unset the event dispatcher for models.
     *
     * @return void
     */
    public static function unsetEventDispatcher(): void
    {
        static::$dispatcher = null;
    }

    /**
     * Execute a callback without firing any model events for any model type.
     *
     * @param  callable  $callback 
     * @return mixed
     */
    public static function withoutEvents(callable $callback)
    {
        $dispatcher = static::getEventDispatcher();

        if ($dispatcher) {
            static::setEventDispatcher(new NullDispatcher($dispatcher));
        }

        try {
            return $callback();
        } finally {
            if ($dispatcher) {
                static::setEventDispatcher($dispatcher);
            }
        }
    }
}