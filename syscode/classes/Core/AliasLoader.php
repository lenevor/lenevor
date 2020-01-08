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
 * @since       0.1.0
 */

namespace Syscode\Core;

/**
 * Receives all the facade classes available for be load use a services provider.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class AliasLoader
{
    /**
     * The singleton instance of loader.
     * 
     * @var \Syscode\Core\AliasLoader $instance
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
     * @return \Syscode\Core\AliasLoader
     */
    public static function getInstance(array $aliases = []) 
    {
        if (is_null(static::$instance))
        {
            return static::$instance = new static($aliases);
        }

        $aliases = array_merge(static::$instance->getAliases(), $aliases);

        static::$instance->setAliases($aliases);

        return static::$instance;
    }

    /**
     * Set the value of the singleton alias loader.
     * 
     * @param  \Syscode\Core\AliasLoader  $loader
     * 
     * @return void
     */
    public static function setInstance($loader)
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
    public static function setFacadeNamespace($namespace)
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
        if (static::$facadeNamespace && strpos($alias, static::$facadeNamespace) === 0) 
        {
            $this->loadFacade($alias);
            return true;
        }

        if (isset($this->aliases[$alias])) 
        {
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
        if (file_exists($path = storagePath('cache/facade-'.sha1($alias).'.php'))) 
        {
            return $path;
        }
        
        file_put_contents($path, $this->formatFacadeStub($alias, file_get_contents(__DIR__.'/stubs/facade.stub')));
        
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
    protected function formatFacadeStub($alias, $stub)
    {
        $replacements = [
            str_replace('/', '\\', dirname(str_replace('\\', '/', $alias))),
            classBasename($alias),
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
        if ( ! $this->registered)
        {
            $this->registeredLoaderStack();

            $this->registered = true;
        }
    }

    /**
     * The load method to the auto-loader stack.
     * 
     * @return void
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
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Set the registered aliases.
     * 
     * @return void
     */
    public function setAliases(array $aliases)
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
    public function alias($class, $alias)
    {
        $this->aliases[$class] = $alias;
    }

    /**
     * Indicated if a loader has been registered.
     * 
     * @return bool
     */
    public function isRegistered()
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
    public function setResgistered($value)
    {
        $this->registered = $value;
    }

    /**
     * Private clone.
     * 
     * @return void
     */
    private function __clone() {}
}