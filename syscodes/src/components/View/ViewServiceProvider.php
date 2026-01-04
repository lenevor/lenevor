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

namespace Syscodes\Components\View;

use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\View\Engines\EngineResolver;
use Syscodes\Components\View\Engines\FileEngine;
use Syscodes\Components\View\Engines\PhpEngine;
use Syscodes\Components\View\Engines\TranspilerEngine;
use Syscodes\Components\View\Transpilers\PlazeTranspiler;

/**
 * For loading the classes from the container of services.
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->registerView();
        $this->registerViewFinder();
        $this->registerPlazeTranspiler();
        $this->registerEngineResolver();
    }

    /**
     * Register the view environment.
     * 
     * @return void
     */
    public function registerView()
    {
        $this->app->singleton('view', function ($app) {
            // The resolver will be used by an environment to get each of the various 
            // engine implementations such as plain PHP or Plaze engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];
            
            $events = $app['events'];

            $factory = new Factory($resolver, $finder, $events);

            $factory->setContainer($app);

            $factory->share('app', $app);

            return $factory;

        });
    }

    /**
     * Register the view finder implementation.
     * 
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', fn ($app) => new FileViewFinder($app['files'], $app['config']['view.paths']));
    }

    /**
     * Register the Plaze transpiler implementation.
     * 
     * @return void
     */
    public function registerPlazeTranspiler()
    {
        $this->app->singleton('plaze.transpiler', fn ($app) => new PlazeTranspiler($app['files'], $app['config']['view.transpiled']));
    }
    
    /**
     * Register the engine resolver instance.
     * 
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver;

            // Register of the various view engines with the resolver
            foreach (['file', 'php', 'plaze'] as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver);
            }
            
            return $resolver;

        });
    }
    
    /**
     * Register the file engine implementation.
     * 
     * @param  \Syscodes\Components\View\Engines\EngineResolver  $resolver
     * 
     * @return void
     */
    public function registerFileEngine($resolver)
    {
        $resolver->register('file', fn () => new FileEngine);
    }
    
    /**
     * Register the PHP engine implementation.
     * 
     * @param  \Syscodes\Components\View\Engines\EngineResolver  $resolver
     * 
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', fn () => new PhpEngine($this->app['files']));
    }
    
    /**
     * Register the Plaze engine implementation.
     * 
     * @param  \Syscodes\Components\View\Engines\EngineResolver  $resolver
     * 
     * @return void
     */
    public function registerPlazeEngine($resolver)
    {
        $resolver->register('plaze', fn () => new TranspilerEngine($this->app['plaze.transpiler'], $this->app['files']));
    }
}