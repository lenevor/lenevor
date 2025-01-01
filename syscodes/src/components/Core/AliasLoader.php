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

/**
 * Receives all the facade classes available for be load use a services provider.
 */
class AliasLoader
{
    /**
     * The singleton instance of loader.
     * 
     * @var \Syscodes\Components\Core\AliasLoader $instance
     */
    protected static $instance;

    /**
     * The namespace for all real-time facades.
     * 
     * @var string $facadeNamespace
     */
    protected static $facadeNamespace = 'Facades\\';

    /**
     * This array of class aliases.
     * 
     * @var array $aliases
     */
    protected $aliases;

    /**
     * Indicates if a loader has been registered.
     * 
     * @var bool $registered
     */
    protected $registered = false;

    /**
     * Get o create the singleton alias loader instance.
     * 
     * @param  array  $aliases
     * 
     * @return \Syscodes\Components\Core\AliasLoader
     */
    public static function getInstance(array $aliases = []) 
    {
        if (is_null(static::$instance)) {
            return static::$instance = new static($aliases);
        }

        $aliases = array_merge(static::$instance->getAliases(), $aliases);

        static::$instance->setAliases($aliases);

        return static::$instance;
    }

    /**
     * Set the value of the singleton alias loader.
     * 
     * @param  \Syscodes\Components\Core\AliasLoader  $loader
     * 
     * @return void
     */
    public static function setInstance($loader): void
    {
        static::$instance = $loader;
    }

    /**
     * Set the real-time facade namespace.
     * 
     * @param  string  $namespace
     * 
     * @return void
     */
    public static function setFacadeNamespace($namespace): void
    {
        static::$facadeNamespace = rtrim($namespace, '\\').'\\';
    }

    /**
     * Constructor. Create a new AliasLoader instance.
     * 
     * @param  array  $aliases
     * 
     * @return void
     */
    private function __construct($aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * Load a class alias if it is registered.
     *
     * @param  string  $alias
     * 
     * @return bool|null
     */
    public function load($alias)
    {
        if (static::$facadeNamespace && strpos($alias, static::$facadeNamespace) === 0) {
            $this->loadFacade($alias);
            return true;
        }

        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }
    }

    /**
     * Load a real-time facade for the given alias.
     *
     * @param  string  $alias
     * 
     * @return void
     */
    protected function loadFacade($alias)
    {
        require $this->ensureFacadeExists($alias); 
    }

    /**
     * Ensure that the given alias has an existing real-time facade class.
     *
     * @param  string  $alias
     * 
     * @return string
     */
    protected function ensureFacadeExists($alias)
    {
        if (file_exists($path = storagePath('cache/facade-'.sha1($alias).'.php'))) {
            return $path;
        }
        
        file_put_contents($path, $this->formatFacadeStub($alias, file_get_contents(__DIR__.'/Templates/facade.tpl')));
        
        return $path;
    }

    /**
     * Format the facade stub with the proper namespace and class.
     *
     * @param  string  $alias
     * @param  string  $stub
     * 
     * @return string
     */
    protected function formatFacadeStub($alias, $stub): string
    {
        $replacements = [
            str_replace('/', '\\', dirname(str_replace('\\', '/', $alias))),
            class_basename($alias),
            substr($alias, strlen(static::$facadeNamespace)),
        ];

        return str_replace(['DummyNamespace', 'DummyClass', 'DummyTarget'], $replacements, $stub);
    }

    /**
     * Register the loader on the auto-loader stack.
     * 
     * @return void
     */
    public function register()
    {
        if ( ! $this->registered) {
            $this->registeredLoaderStack();

            $this->registered = true;
        }
    }

    /**
     * The load method to the auto-loader stack.
     * 
     * @return bool
     */
    protected function registeredLoaderStack()
    {
        spl_autoload_register([$this, 'load'], true, true);
    }

    /**
     * Get the registered aliases.
     * 
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Set the registered aliases.
     * 
     * @return void
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * Add an alias to the loader.
     * 
     * @param  string  $class
     * @param  string  $alias
     * 
     * @return void
     */
    public function alias($class, $alias): void
    {
        $this->aliases[$class] = $alias;
    }

    /**
     * Indicated if a loader has been registered.
     * 
     * @return bool
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Set the registered state of the loader.
     * 
     * @param  bool  $value
     * 
     * @return void
     */
    public function setResgistered($value): void
    {
        $this->registered = $value;
    }

    /**
     * Magin method.
     * 
     * Private clone.
     * 
     * @return void
     */
    private function __clone() {}
}