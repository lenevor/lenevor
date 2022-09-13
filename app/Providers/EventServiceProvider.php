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
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace App\Providers;

use Syscodes\Components\Core\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Manage all events occurred in the application.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     * 
     * @var array $listen
     */
    protected $listen = [
        //
    ];

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
}