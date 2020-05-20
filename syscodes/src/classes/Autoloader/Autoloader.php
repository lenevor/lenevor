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
 * @since       0.1.1 
 */

namespace Syscodes;

use Syscodes\Config\AutoloadConfig;

/**
 * Lenevor Autoloader
 *
 * An autoloader that uses both PSR4 autoloading, array of files, and traditional classmaps.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
     * List map of classes or namespaces.
     * 
     * @var array $classOrNamespaceListMap
     */
    protected $classOrNamespaceListMap = [
        BST_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadPsr4.php',
        BST_PATH.'register'.DIRECTORY_SEPARATOR.'autoloadClassmap.php',
    ];

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
     * @uses   \Syscodes\Config\AutoloadConfig
     */
    public function initialize(AutoloadConfig $config)
    {
        if (isset($config->psr4))
        {
           $this->prefixes = $config->addPsr4((array) $this->classOrNamespaceListMap[0]);
        }

        if (isset($config->classmap))
        {
            $this->classmap = $config->addClassMap((array)  $this->classOrNamespaceListMap[1]);
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
     * @param  string  $path  The path 
     *
     * @return void
     */
    public function addNamespace(string $namespace, string $path)
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
    public function addClasses(array $classes)
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
     * @param  string  $file
     * 
     * @return mixed
     */
    public function getAutoloaderFileRequire($fileIdentifier, string $file)
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
    public function loadClass(string $class)
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
    protected function loadInNamespace(string $class)
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
            
            if (0 === strpos($class, $namespace))
            {
                foreach ($directories as $directory) 
                {
                    if (false !== $lastPos = strrpos($namespace, '\\'))
                    {
                        $pathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR).'.php';
                        $filePath = $directory.substr($pathPsr4, $lastPos + 1);
                        $filename = $this->sendFilePath($filePath); 
                    }

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
    protected function loadLegacy(string $class)
    {
        // If there is a namespace on this class, then
        // we cannot load it from traditional locations.
        if (strpos($class, '\\') !== false)
        {
            return false;
        }

        $paths = [
            APP_PATH.'Models/',
            APP_PATH.'Console/',
            APP_PATH.'Http/Controllers/',
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
    public function removeNamespace(string $namespace)
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
    protected function sendFilePath(string $file)
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
        
        spl_autoload_register(function ($class) use ($config) 
        {
            if (empty($config[$class]))
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