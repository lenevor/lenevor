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

namespace Syscodes\Components\Database;

use Syscodes\Components\Database\Erostrine\Model;
use Syscodes\Components\Support\ServiceProvider;

/**
 * For loading the classes from the container of services.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the Application events.
     * 
     * @return void
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);
    }

    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->registerConfigurationServices();
    }

    /**
     * Register the primary database bindings.
     * 
     * @return void
     */
    protected function registerConfigurationServices()
    {
        // The connection factory is used to create the actual connection instances on
        // the database.
        $this->app->singleton('db.factory', fn ($app) => new ConnectionFactory($app));

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed.
        $this->app->singleton('db', fn ($app) => new DatabaseManager($app, $app['db.factory']));
        
        $this->app->bind('db.connection', fn ($app) => $app['db']->connection());

        $this->app->bind('db.schema', fn ($app) => $app['db']->connection()->getSchemaBuilder());

        $this->app->singleton('db.transactions', fn ($app) => new DatabaseTransactionsManager);
    }
}