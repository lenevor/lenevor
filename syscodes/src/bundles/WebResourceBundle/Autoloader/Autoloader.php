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

namespace Syscodes\Bundles\WebResourceBundle\Autoloader;

use InvalidArgumentException;

/**
 * Lenevor Autoloader.
 *
 * An autoloader that uses both PSR4 autoloading, array of files, and traditional classmaps.
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
     * @var array $files
     */
    protected $files = [];

    /** 
     * This is all the namepaces paths.
     * 
     * @var array $prefixes
     */
    protected $prefixes = [];

    /**
     * The class instance.
     * 
     * @var string $instance
     */
    public static $instance;

    /**
     * The class instance.
     * 
     * @return static
     */
    public static function instance()
    {
        if (empty(static::$instance)) { 
            static::$instance = new static;
        }

        return new static;
    }

    /**
     * Initialize variables of configuration.
     * 
     * @param  \Syscodes\Bundles\WebResourceBundle\Autoloader\Autoload  $config
     *
     * @return self
     */
    public function initialize(Autoload $config): self
    {
        $this->prefixes = [];
        $this->classmap = [];
        $this->files    = [];

        if (empty($config->psr4) && empty($config->classmap) && empty($config->files)) {
            throw new InvalidArgumentException(
                'Config array must contain either the \'psr4\' key or the \'classmap\' key or the \'files\' key'
            );
        }

        if (isset($config->psr4)) {
            $this->addNamespace($config->psr4);
        }

        if (isset($config->classmap)) {
            $this->classmap = $config->classmap;
        }

        if (isset($config->files)) {
            $this->files = $config->files;
        }

        if ($config->enabledInComposer) {
           $this->enabledComposerNamespaces();
        }

        return $this;
    }


    /**
     * Initiates the start of classes.
     *
     * @return bool
     */
    public function register()
    {
        // Prepend the PSR4  autoloader for maximum performance
        spl_autoload_register([$this, 'loadClass'], true, true);
        
        // Now prepend another loader for the files in our class map
        spl_autoload_register([$this, 'loadClassmap'], true, true);

        // Autoloading for the files helpers, hooks or functions
        foreach ($this->files as $fileIdentifier => $file) {
            $this->getAutoloaderFileRequire($fileIdentifier, $file);
        }
    }

    /**
     * Registers a namespace with the autoloader.
     *
     * @param  array|string  $namespace  The namespace
     * @param  string|null  $path  The path
     *
     * @return self
     */
    public function addNamespace($namespace, ?string $path = null): self
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $namespacePath) {
                $prefix = trim($prefix, '\\');

                if (is_array($namespacePath)) {
                    foreach ($namespacePath as $dir) {
                        $this->prefixes[$prefix][] = rtrim($dir, '\\/').DIRECTORY_SEPARATOR;
                    }

                    continue;
                }
                
                $this->prefixes[$prefix][] = rtrim($namespacePath, '\\/').DIRECTORY_SEPARATOR;
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
    public function getNamespace(?string $prefix = null): array
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
     * @return self
     */
    public function removeNamespace(string $namespace): self
    {
        if (isset($this->prefixes[trim($namespace, '\\')])) {
            unset($this->prefixes[trim($namespace, '\\')]);
        }
        
        return $this;
    }

    /**
     * Load a class using available class mapping.
     * 
     * @param  string  $class  The classname to load
     * 
     * @return mixed
     */
    public function loadClassmap(string $class)
    {
        $file = $this->classmap[$class] ?? '';
        
        if (is_string($file) && $file !== '') {
            return $this->sendFilePath($file);
        }
        
        return false;
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
            $this->sendFilePath($file);
            
            $GLOBALS['__lenevor_autoload_files'][$fileIdentifier] = true;
        }
    }

    /**
     * Loads a class.
     *
     * @param  string  $class  The classname to load
     *
     * @return mixed   
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
        if (false === strpos($class, '\\')) {
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
    protected function sendFilePath(string $file): bool
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
    public function sanitizeFile(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9\s\/\-\_\.\:\\\\]/', '', $filename);

        // Clean up our filename edges.
        $filename = trim($filename, '.-_');

        return $filename;
    }

    /**
     * Locates all PSR4 compatible namespaces from Composer.
     * 
     * @return mixed
     */
    protected function enabledComposerNamespaces()
    {
        if ( ! is_file(COMPOSER_PATH)) {
            return false;
        }

        $composer = include COMPOSER_PATH;        
        $paths    = $composer->getPrefixesPsr4();
        $classes  = $composer->getClassMap();

        unset($composer);
        
        // Get rid of Lenevor so we don't have duplicates
        if (isset($paths['Syscodes\\Components\\'])) {
            unset($paths['Syscodes\\Components\\']);
        }
        
        // Composer stores namespaces with trailing slash. We don't
        $newPaths = [];
        
        foreach ($paths as $key => $value) {
            $newPaths[rtrim($key, '\\')] = $value;
        }
        
        $this->prefixes = array_merge($this->prefixes, $newPaths);
        $this->classmap = array_merge($this->classmap, $classes);
    }
}