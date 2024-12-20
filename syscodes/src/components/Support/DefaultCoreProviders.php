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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

/**
 * Gets the default all services providers of the framework.
 */
class DefaultCoreProviders
{
    /**
     * The default providers.
     * 
     * @var array $providers
     */
    protected $providers = [];

    /**
     * Constructor. Create a new DefaultCoreProviders class instance.
     * 
     * @param  array|null  $providers
     * 
     * @return void
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?: [
            \Syscodes\Components\Auth\AuthServiceProvider::class,
            \Syscodes\Components\Cache\CacheServiceProvider::class,
            \Syscodes\Components\Core\Providers\ConsoleServiceProvider::class,
            \Syscodes\Components\Cookie\CookieServiceProvider::class,
            \Syscodes\Components\Database\DatabaseServiceProvider::class,
            \Syscodes\Components\Debug\DebugServiceProvider::class,
            \Syscodes\Components\Encryption\EncryptionServiceProvider::class,
            \Syscodes\Components\Events\EventServiceProvider::class,
            \Syscodes\Components\Filesystem\FilesystemServiceProvider::class,
            \Syscodes\Components\Hashing\HashServiceProvider::class,
            \Syscodes\Components\Pagination\PaginationServiceProvider::class,
            \Syscodes\Components\Pipeline\PipelineServiceProvider::class,
            \Syscodes\Components\Session\SessionServiceProvider::class,
            \Syscodes\Components\Translation\TranslationServiceProvider::class,
            \Syscodes\Components\Validation\ValidationServiceProvider::class,
            \Syscodes\Components\View\ViewServiceProvider::class,
        ];
    }

    /**
     * Merge the provider collection with the given providers.
     * 
     * @param  array  $providers
     * 
     * @return static
     */
    public function merge(array $providers): static
    {
        $this->providers = array_merge($this->providers, $providers);

        return new static($this->providers);
    }
    
    /**
     * Replace the provider collection with other given providers.
     * 
     * @param  array  $replacements
     * 
     * @return static
     */
    public function replace(array $replacements): static
    {
        $current = collect($this->providers);
        
        foreach ($replacements as $from => $to) {
            $key = $current->search($from);
            
            $current = $key ? $current->replace([$key => $to]) : $current;
        }
        
        return new static($current->values()->toArray());
    }

    /**
     * Get the collection of providers as an array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->providers;
    }
}