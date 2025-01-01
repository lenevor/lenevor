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

namespace Syscodes\Components\Core\Support\Providers;

use Syscodes\Components\Support\Facades\Event;
use Syscodes\Components\Support\ServiceProvider;

/**
 * Manage all events occurred in the application.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     * 
     * @var array $listen
     */
    protected $listen = [];

    /**
     * The subscriber classes to register.
     * 
     * @var array $suscribe
     */
    protected $subscribe = [];

    /**
     * Bootstrap any application services.
     * 
     * Note: Events - all standard Events are defined here, as 
     * determined for all the framework using closures. 
     * 
     * You add the 'Event' facade with the purpose call the Listen method 
     * and generate actions in custom events for all the application's 
     * which it is developing, if desired.
     * 
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register()
    {
        $this->booting(function () {
            $events = $this->listens();

            foreach ((array) $events as $event => $listeners) {
                foreach ($listeners as $listener) {
                    Event::listen($event, $listener);
                }
            }
            
            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }
        });
    }

    /**
     * Get the events and handlers.
     * 
     * @return array
     */
    public function listens(): array
    {
        return $this->listen;
    }
}