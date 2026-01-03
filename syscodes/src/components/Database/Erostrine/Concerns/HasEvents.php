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

/**
 * HasEvents.
 */
trait HasEvents
{
    /**
     * The event map for the model.
     * 
     * @var array $dispatchEvents
     */
    protected $dispatchEvents = [];
    
    /**
     * User exposed observable events.
     * 
     * @var array $observables
     */
    protected $observables = [];
    
    /**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher $dispatcher
	 */
	protected static $dispatcher;

    /**
     * Get the observable event names.
     * 
     * @return array
     */
    public function getObservableEvents(): array
    {
        return array_merge(
            [
                'creating', 'created', 'updating', 'updated',
                'deleting', 'deleted', 'saving', 'saved', 
            ],
            $this->observables
        );
    }

    /**
     * Set the observable event names.
     * 
     * @param  array  $observables
     * 
     * @return static
     */
    public function setObservableEvents(array $observables): static
    {
        $this->observables = $observables;

        return $this;
    }

    /**
     * Add an observable event name.
     * 
     * @param  mixed  $observables
     * 
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
     * @param  mixed  $observables
     * 
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
     * 
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
     * 
     * @return mixed
     */
    public function fireModelEvent($event, bool $detain = true)
    {
        if ( ! isset(static::$dispatcher)) {
            return true;
        }

        $method = $detain ? 'until' : 'dispatch';

        return static::$dispatcher->{$method}("erostrine.{$event}: ".static::class, $this);
    }

    /**
     * Register a creating model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function creating($callback): void
    {
        static::registerModelEvent('creating', $callback);
    }

    /**
     * Register a created model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function created($callback): void
    {
        static::registerModelEvent('created', $callback);
    }

    /**
     * Register a updating model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function updating($callback): void
    {
        static::registerModelEvent('updating', $callback);
    }

    /**
     * Register a updated model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function updated($callback): void
    {
        static::registerModelEvent('updated', $callback);
    }

    /**
     * Register a deleting model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function deleting($callback): void
    {
        static::registerModelEvent('deleting', $callback);
    }

    /**
     * Register a deleted model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function deleted($callback): void
    {
        static::registerModelEvent('deleted', $callback);
    }

    /**
     * Register a saving model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function saving($callback): void
    {
        static::registerModelEvent('saving', $callback);
    }

    /**
     * Register a saved model event with the dispatcher.
     * 
     * @param  \Closure|string  $callback
     * 
     * @return void
     */
    public static function saved($callback): void
    {
        static::registerModelEvent('saved', $callback);
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
        
        foreach ((new static)->getObservableEvents() as $event) {
            static::$dispatcher->delete("erostrine.{$event}: ".static::class);
        }
        
        foreach (array_values((new static)->dispatchEvents) as $event) {
            static::$dispatcher->delete($event);
        }
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
     * 
     * @return void
     */
    public static function setEventDispatcher(Dispatcher $dispatcher)
    {
        return static::$dispatcher = $dispatcher;
    }
}