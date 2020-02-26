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

namespace App\Providers;

use Syscode\Support\Facades\Event;
use Syscode\Core\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Manage all events occurred in the application.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
     * @return  void
     */
    public function boot()
    {
        parent::boot();

        $path = $this->app->path('Events/Events.php');

        $this->loadEventPath($path);
    }
}