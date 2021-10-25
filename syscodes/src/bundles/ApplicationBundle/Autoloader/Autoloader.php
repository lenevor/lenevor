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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Bundles\ApplicationBundle\Autoloader;

use InvalidArgumentException;

/**
 * Lenevor Autoloader
 *
 * An autoloader that uses both PSR4 autoloading, array of files, and traditional classmaps.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Autoloader 
{
    /** 
     * Array of classmap.
     * 
     * @var array $classmap
     */
    protected $classmap = [];

    /** 
     * Array of files.
     * 
     * @var array $includeFiles
     */
    protected $includeFiles = [];

    /** 
     * This is all the namepaces paths.
     * 
     * @var array $prefixes
     */
    protected $prefixes = [];

    /**
     * Initialize variables of configuration.
     * 
     * @param  \Syscodes\Bundles\ApplicationBundle\Autoload  $config
     *
     * @return $this
     */
    public function initialize(Autoload $config)
    {
        if (isset($config->psr4)) {
            $this->addNamespace($config->psr4);
        }

        if (isset($config->classmap)) {
            $this->classmap = $config->classmap;
        }

        if (isset($config->includeFiles)) {
            $this->includeFiles = $config->includeFiles;
        }

        if ($config->enabledInComposer) {
            $this->enabledComposerNamespaces();
        }

        return $this;
    }

    /**
     * Registers a namespace with the autoloader.
     *
     * @param  array|string  $namespace  The namespace
     * @param  string|null  $path  The path
     *
     * @return $this
     */
    public function addNamespace($namespace, string $path = null)
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $path) {
                $prefix = trim($prefix, '\\');

                if (is_array($path)) {
                    foreach ($path as $dir) {
                        $this->prefixes[$prefix][] = rtrim($dir, '\\/').DIRECTORY_SEPARATOR;
                    }

                    continue;
                }

                $this->prefixes[$prefix][] = rtrim($path, '\\/').DIRECTORY_SEPARATOR;
            }
        } else {
            $this->prefixes[trim($namespace, '\\')][] = rtrim($path, '\\/').DIRECTORY_SEPARATOR;
        }

        return $this;
    }

    /**
     * Get namespaces with prefixes as keys and paths as values.
     * 
     * @param  string|null  $prefix
     *
     * @return array
     */
    public function getNamespace(string $prefix = null)
    {
        if (null === $prefix) {
            return $this->prefixes;
        }

        return $this->prefixes[trim($prefix, '\\')] ?? [];
    }

    /**
     * Removes a single namespace from the psr4 settings.
     * 
     * @param  string  $namespace
     * 
     * @return $this
     */
    public function removeNamespace(string $namespace)
    {
        unset($this->prefixes[trim($namespace, '\\')]);

        return $this;
    }
    
    /**
     * Loader of files with ID global.
     * 
     * @param  bool|int  $fileIdentifier
     * @param  string  $file
     * 
     * @return mixed
     */
    public function getAutoloaderFileRequire($fileIdentifier, string $file)
    {
        if (empty($GLOBALS['__lenevor_autoload_files'][$fileIdentifier])) {
            require $file;
            
            $GLOBALS['__lenevor_autoload_files'][$fileIdentifier] = true;
        }
    }

    /**
     * Loads a class.
     *
     * @param  string  $class  The classname to load
     *
     * @return string   
     */
    public function loadClass(string $class)
    {
        $class = trim($class, '\\');
        
        $class = str_ireplace('.php', '', $class);

        return $this->loadInNamespace($class);
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param  string  $class  The fully-qualified class name
     *
     * @return mixed
     */
    protected function loadInNamespace(string $class)
    {
        if (strpos($class, '\\') === false) {
            // Attempts to load the class from common locations in previous
            // version of Lenevor, namely: 
            // 'app/Console', 
            // 'app/Models', 
            // 'app/Exceptions',
            // 'app/Http/Controllers',
            // 'app/Http/Middleware', 
            // 'app/Events,
            // 'app/Listeners.
            $class    = 'App\\'.$class;
            $filePath = APP_PATH.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            $filename = $this->sendFilePath($filePath);

            if ($filename) {
                return $filename;
            }
            
            return false;
        }

        foreach ($this->prefixes as $namespace => $directories) {
            foreach ($directories as $directory) {
                $directory = rtrim($directory, '\\/');

                if (0 === strpos($class, $namespace)) {
                    $filePath = $directory.str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))).'.php';                                
                    $filename = $this->sendFilePath($filePath); 

                    if ($filename) {
                        return $filename;
                    } 
                }                 
            }
        }

        return false;
    }

    /**
     * A central way to require a file is loaded. Split out primarily
     * for testing purposes.
     *
     * @param  string  $file
     *
     * @return bool
     */
    protected function sendFilePath(string $file)
    {
        $file = $this->sanitizeFile($file);

        if (is_file($file)) {
            include_once $file;

            return $file;
        }

        return false;
    }

    /**
     * Sanitizes a filename, replacing spaces with dashes.
     *
     * Removes special characters that are illegal in filenames on certain
     * operating systems and special characters requiring special escaping
     * to manipulate at the command line. Replaces spaces and consecutive
     * dashes with a single dash. Trim period, dash and underscore from 
     * beginning and end of filename.
     *
     * @param  string  $filename
     *
     * @return string    
     */
    public function sanitizeFile(string $filename)
    {
        $filename = preg_replace('/[^a-zA-Z0-9\s\/\-\_\.\:\\\\]/', '', $filename);

        // Clean up our filename edges.
        $filename = trim($filename, '.-_');

        return $filename;
    }

    /**
     * Initiates the start of classes.
     *
     * @return bool
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
        
        // Now prepend another loader for the files in our class map
        $config = is_array($this->classmap) ? $this->classmap : [];
        
        spl_autoload_register(function ($class) use ($config) {

            if (empty($config[$class])) {
                return false;
            }

            include_once $config[$class];

        }, true, // Throw exception
           true // Prepend
        );

        // Autoloading for the files helpers, hooks or functions
        $files = is_array($this->includeFiles) ? $this->includeFiles : [];

        spl_autoload_register(function () use ($files) {

            foreach ($files as $fileIdentifier => $file) {
                $this->getAutoloaderFileRequire($fileIdentifier, $file);                
            }
            
        }, true, 
           true
        );
    }

    /**
     * Locates all PSR4 compatible namespaces from Composer.
     * 
     * @return void
     */
    protected function enabledComposerNamespaces()
    {
        if ( ! is_file(COMPOSER_PATH)) {
            return false;
        }

        $composer = include COMPOSER_PATH;
        
        $paths = $composer->getPrefixesPsr4();
        unset($composer);
        
        // Get rid of Lenevor so we don't have duplicates
        if (isset($paths['Syscodes\\'])) {
            unset($paths['Syscodes\\']);
        }
        
        // Composer stores namespaces with trailing slash. We don't
        $newPaths = [];
        
        foreach ($paths as $key => $value) {
            $newPaths[rtrim($key, '\\ ')] = $value;
        }
        
        $this->prefixes = array_merge($this->prefixes, $newPaths);
    }
}