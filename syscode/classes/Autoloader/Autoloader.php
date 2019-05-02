<?php 

namespace Syscode;

use Syscode\Config\AutoloadConfig;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0 
 */
class Autoloader 
{
    /** 
     * Array of classmap
     * 
     * @var array $classmap
     */
    protected $classmap = [];

    /** 
     * Array of files
     * 
     * @var array $includeFiles
     */
    protected $includeFiles = [];

    /** 
     * This is all the namepaces paths
     * 
     * @var array $prefixes
     */
    protected $prefixes = [];

    /**
     * Initialize variables of configuration.
     * 
     * @param  array  $config
     *
     * @return array
     *
     * @uses   \Syscode\Config\AutoloadConfig
     */
    public function initialize(AutoloadConfig $config)
    {
        if (isset($config->psr4))
        {
           $this->prefixes = $config->psr4;
        }

        if (isset($config->classmap))
        {
            $this->classmap = $config->classmap;
        }

        if (isset($config->includeFiles))
        {
            $this->includeFiles = $config->includeFiles;
        }
        
        unset($config);

        return $this;
    }

    /**
     * Registers a namespace with the autoloader.
     *
     * @param  string  $namespace  The namespace
     * @param  string  $path       The path 
     *
     * @return void
     */
    public function addNamespace($namespace, $path)
    {
        if (isset($this->prefixes[$namespace]))
        {
            if (is_string($this->prefixes[$namespace]))
            {
                $this->prefixes[$namespace] = [$this->prefixes[$namespace]];
            }

            $this->prefixes[$namespace] = array_merge($this->prefixes[$namespace], [$path]);
        }
        else
        {
            $this->prefixes[$namespace] = [$path];
        }

        return $this->prefixes[$namespace];
    }

    /**
     * Adds multiple class paths to the load path.
     * 
     * @param  array  $classes  The classnames and paths 
     *
     * @return void
     */
    public function addClasses($classes)
    {
        foreach ($classes as $class => $path) 
        {
           $this->classmap[ucfirst($class)] = $path;
        }
    }
    
    /**
     * Loader of files with ID global.
     * 
     * @param  bool|int  $fileIdentifier
     * @param  string    $file
     * 
     * @return mixed
     */
    public function getAutoloaderFileRequire($fileIdentifier, $file)
    {
        if (empty($GLOBALS['__lenevor_autoload_files'][$fileIdentifier]))
        {
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
    public function loadClass($class)
    {
        $class = trim($class, '\\');
        
        $class = str_ireplace('.php', '', $class);

        $mapFile = $this->loadInNamespace($class);

        if ( ! $mapFile)
        {
            $mapFile = $this->loadLegacy($class);
        }

        return $mapFile;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param  string  $class  The fully-qualified class name
     *
     * @return mixed
     */
    protected function loadInNamespace($class)
    {
        if (strpos($class, '\\') === 0)
        {
            return true;
        }

        foreach ($this->prefixes as $namespace => $directories) 
        {
            if (is_string($directories))
            {
                $directories = [$directories];
            }

            foreach ($directories as $directory) 
            {
                $directory = rtrim($directory, DIRECTORY_SEPARATOR);

                if (strpos($class, $namespace) === 0)
                {
                    $filePath = $directory.str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($namespace))).'.php';

                    $filename = $this->sendFilePath($filePath);

                    if ($filename)
                    {
                        return $filename;
                    }
                }                
            }
        }
    }

    /**
     * Attempts to load the class from common locations in previous
     * version of Lenevor, namely 'app/Console', and
     * 'app/Models'.
     * 
     * @param  string  $class  The class name. This typically should NOT have a namespace.
     *
     * @return mixed  
     */
    protected function loadLegacy($class)
    {
        // If there is a namespace on this class, then
        // we cannot load it from traditional locations.
        if (strpos($class, '\\') !== false)
        {
            return false;
        }

        $paths = [
            APP_PATH.'Http/Controllers/',
            APP_PATH.'Console/',
        ];

        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

        foreach ($paths as $path)
        {
            if ($file = $this->sendFilePath($path.$class))
            {
                return $file;
            }
        }

        return false;
    }

    /**
     * Removes a single namespace from the psr4 settings.
     *
     * @param  string  $namespace
     *
     * @return $this
     */
    public function removeNamespace($namespace)
    {
        unset($this->prefixes[$namespace]);

        return $this;
    }

    /**
     * A central way to require a file is loaded. Split out primarily
     * for testing purposes.
     *
     * @param  string  $file
     *
     * @return bool
     */
    protected function sendFilePath($file)
    {
        $file = $this->sanitizeFile($file);

        if (file_exists($file))
        {
            require_once $file;

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
    public function sanitizeFile($filename)
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
        
        spl_autoload_register(function ($class) use ($config) 
        {
            if ( ! array_key_exists($class, $config))
            {
                return false;
            }

            include_once $config[$class];

        }, true, // Throw exception
           true // Prepend
        );

        // Autoloading for the files helpers, hooks or functions
        $files = is_array($this->includeFiles) ? $this->includeFiles : [];

        foreach ($files as $fileIdentifier => $file)
        {
            $this->getAutoloaderFileRequire($fileIdentifier, $file);
        }
    }
}