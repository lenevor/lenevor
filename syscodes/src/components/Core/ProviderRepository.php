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

namespace Syscodes\Components\Core;

use Exception;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Contracts\Core\Application;

/**
 * Allows the register of a application service manifest file.
 */
class ProviderRepository
{
    /**
     * The application implementation.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $app
     */
    protected $app;

    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Components\Filesystem\Filesystem $files
     */
    protected $files;

    /**
     * The path to the manifest file.
     * 
     * @var string $manifestPath
     */
    protected $manifestPath;
    
    /**
     * Constructor. Create a new ProviderRepository class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $app
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  string  $manifestPath
     * 
     * @return void
     */
    public function __construct(Application $app, Filesystem $files, $manifestPath)
    {
        $this->app          = $app;
        $this->files        = $files;
        $this->manifestPath = $manifestPath;
    }
    
    /**
     * Register the application service providers.
     * 
     * @param  array  $providers
     * 
     * @return void
     */
    public function load(array $providers)
    {
        $manifest = $this->loadManifest();
        
        if ($this->shouldRecompile($manifest, $providers)) {
            $manifest = $this->compileManifest($providers);
        }
        
        foreach ($manifest['when'] as $provider => $events) {
            $this->registerLoadEvents($provider, $events);
        } 
        
        foreach ($manifest['eager'] as $provider) {
            $this->app->register($provider);
        }

        $this->app->addDeferredServices($manifest['deferred']);
    }
    
    /**
     * Load the service provider manifest JSON file.
     * 
     * @return array|null
     */
    public function loadManifest()
    {
        if ($this->files->exists($this->manifestPath)) {
            $manifest = $this->files->getRequire($this->manifestPath);
            
            if ($manifest) {
                return array_merge(['when' => []], $manifest);
            }
        }
    }
    
    /**
     * Determine if the manifest should be compiled.
     * 
     * @param  array  $manifest
     * @param  array  $providers
     * 
     * @return bool
     */
    public function shouldRecompile($manifest, $providers): bool
    {
        return is_null($manifest) || $manifest['providers'] != $providers;
    }
    
    /**
     * Register the load events for the given provider.
     * 
     * @param  string  $provider
     * @param  array  $events
     * 
     * @return void
     */
    protected function registerLoadEvents($provider, array $events): void
    {
        if (count($events) < 1) {
            return;
        }
        
        $this->app->make('events')->listen($events, fn () => $this->app->register($provider));
    }
    
    /**
     * Compile the application service manifest file.
     * 
     * @param  array  $providers
     * 
     * @return array
     */
    protected function compileManifest($providers): array
    {
        $manifest = $this->freshManifest($providers);
        
        foreach ($providers as $provider) {
            $instance = $this->createProvider($provider);
            
            if ($instance->isDeferred()) {
                foreach ($instance->provides() as $service) {
                    $manifest['deferred'][$service] = $provider;
                }

                $manifest['when'][$provider] = $instance->when();
            } else {
                $manifest['eager'][] = $provider;
            }
        }
        
        return $this->writeManifest($manifest);
    }
    
    /**
     * Create a fresh service manifest data structure.
     * 
     * @param  array  $providers
     * 
     * @return array
     */
    protected function freshManifest(array $providers): array
    {
        return ['providers' => $providers, 'eager' => [], 'deferred' => []];
    }
    
    /**
     * Write the service manifest file to disk.
     * 
     * @param  array  $manifest
     * 
     * @return array
     * 
     * @throws \Exception
     */
    public function writeManifest($manifest): array
    {
        if ( ! is_writable($dirname = dirname($this->manifestPath))) {
            throw new Exception("The {$dirname} directory must be present and writable.");
        }
        
        $this->files->replace(
            $this->manifestPath, "<?php\n\nreturn ".var_export($manifest, true).';'
        );
        
        return array_merge(['when' => []], $manifest);
    }
    
    /**
     * Create a new provider instance.
     * 
     * @param  string  $provider
     * 
     * @return \Syscodes\Components\Support\ServiceProvider
     */
    public function createProvider($provider)
    {
        return new $provider($this->app);
    }
}