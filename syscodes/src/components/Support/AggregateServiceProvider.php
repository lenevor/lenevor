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

namespace Syscodes\Components\Support;

/**
 * Allows the register of service providers.
 */
class AggregateServiceProvider extends ServiceProvider
{
    /**
     * The provider class names.
     * 
     * @var array $providers
     */
    protected $providers = [];
    
    /**
     * An array of the service provider instances.
     * 
     * @var array $instances
     */
    protected $instances = [];
    
    /**
     * Register the service provider.
     * 
     * @return void
     */
    public function register()
    {
        $this->instances = [];
        
        foreach ($this->providers as $provider) {
            $this->instances[] = $this->app->register($provider);
        }
    }
    
    /**
     * Get the services provided by the provider.
     * 
     * @return array
     */
    public function provides(): array
    {
        $provides = [];
        
        foreach ($this->providers as $provider) {
            $instance = $this->app->resolveProviderClass($provider);
            
            $provides = array_merge($provides, $instance->provides());
        }
        
        return $provides;
    }
}