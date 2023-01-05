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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

/**
 * Finder allows to find files and directories in all the system.
 */
class Finder
{   
    /**
     * Singleton master instance.
     *
     * @var string|null $instance
     */
    protected static $instance = null;

    /**
     * Include paths that are used to find files.
     * 
     * @var array $paths  
     */
    protected $paths = [];

    /**
     * Gets a singleton instance of Finder.
     *
     * @return \Syscodes\Components\Support\Finder
     */
    public static function instance()
    {
        if ( ! static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * An alias for Finder::instance()->locate().
     *
     * @param  string  $file  The file  
     * @param  string|null  $directory  The directory
     * @param  string  $extension  The file extension  
     *
     * @return mixed  Path, or paths, or false
     */
    public static function locate(string $file, string $directory = null, $extension = 'php')
    {
        return static::instance()->locateFile($file, $directory, $extension);
    }
    
     /**
     * An alias for Finder::instance()->search().
     *
     * @param  string  $path  The namespace   
     * @param  string  $extension  The file etension
     * @param  string  $prioritizer  Activate the prioritary
     *
     * @return array  Path, or paths
     */
    public static function search(string $path, string $extension = 'php', bool $prioritizer = true) 
    {
        return static::instance()->searchFile($path, $extension, $prioritizer);       
    }

    /**
     * Returns the search for a directory and file with their respective extension.
     *
     * @param  string  $file  The file
     * @param  string  $directory  The directory
     * @param  string  $extension  The file extension  
     *
     * @return bool|string[]
     */
    public function locateFile(?string $file, ?string $directory = null, string $extension = 'php')
    {
        $file = $this->getExtension($file, $extension);
        
        // Clears the directory name if it is at the beginning of the filename
        if ( ! empty($directory) && strpos($file, $directory) === 0) {
            $file = substr($file, strlen($directory.'/'));
        }
        
        //Is not namespaced? Try the application directory.
        if (strpos($file, '\\') === false) {
            return $this->legacyLocate($file, $directory);
        }
        
        // Standardize slashes to handle nested directories
        $file = strtr($file, '/', '\\');
        $file = ltrim($file, '\\');
        
        $segments = explode('\\', $file);
        
        // The first segment will be empty if a slash started the filename
        if (empty($segments[0])) {
            unset($segments[0]);
        }

        $paths    = [];
        $filename = '';
        $result   = [];

        $namespaces = autoloader()->getNamespace();

        foreach (array_keys($namespaces) as $namespace) {           
            // There may be sub-namespaces of the same vendor,
            // so overwrite them with namespaces found later
            $paths = $namespaces[$namespace];

            $filename = ltrim(str_replace('\\', '/', $file), '/');
        }

        // if no namespaces matched then quit
        if (empty($paths)) {
            return false;
        }
    
        // Check each path in the namespace
        foreach ($paths as $path) {
            // Ensure trailing slash
            $path = rtrim($path, '/');
            $path = dirname($path).DIRECTORY_SEPARATOR;
            
            // If we have a directory name, then the calling function
            // expects this file to be within that directory, like 'Views',
            // or 'libraries'
            if ( ! empty($directory) && strpos($path.$filename, DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR) === false) {
                $path .= trim($directory, '/').'/';
            }
            
            $path .= $filename;
            
            if ( ! is_file($path) && ! file_exists($path)) {
                return $result[] = $path;
            }
        }
        
        return false;
    }
    
    /**
     * Searches through all of the defined namespaces looking for a file.
     * Returns an array of all found locations for the defined file.
     * 
     * Example:
     *  $locator->search('Config/Routes');
     * 
     *  // Assuming PSR4 namespaces include foo and bar, might return:
     *  [
     *      'app/foo/Config/Routes.php',
     *      'app/bar/Config/Routes.php',
     * ]
     * 
     * 
     */
    public function searchFile(string $path, string $extension = 'php', bool $prioritizer = true): array
    {
        $path = $this->getExtension($path, $extension);
        
        $foundPaths = [];
        $appPaths   = [];
        
        foreach ($this->getNamespaces() as $namespace) {
            if (isset($namespace['path']) && is_file($namespace['path'].$path)) {
                $fullPath = $namespace['path'].$path;
                $fullPath = realpath($fullPath) ?: $fullPath;
                
                if ($prioritizer) {
                    $foundPaths[] = $fullPath;
                } elseif (strpos($fullPath, APP_PATH) === 0) {
                    $appPaths[] = $fullPath;
                } else {
                    $foundPaths[] = $fullPath;
                }
            }
        }
        
        if ( ! $prioritizer && ! empty($appPaths)) {
            $foundPaths = [...$foundPaths, ...$appPaths];
        }
        
        // Remove any duplicates
        return array_unique($foundPaths);
    }

    /**
     * Get a extension is at the end of a filename.
     * 
     * @param  string  $path
     * @param  string  $extension
     * 
     * @return string
     */
    protected function getExtension(string $path, string $extension): string
    {
        if ($extension) {
            $extension = '.'.$extension;

            if (substr($path, -strlen($extension)) !== $extension) {
                $path .= $extension;
            }
        }

        return $path;
    }
    
    /**
     * Return the namespace mappings we know about.
     *  
     * @return string[]
     */
    protected function getNamespaces(): array
    {
        $namespaces = [];
        
        // Save system for last
        $system = [];
        
        foreach (autoloader()->getNamespace() as $prefix => $paths) {
            foreach ($paths as $path) {
                if ($prefix === 'Syscodes') {
                    $system = [
                        'prefix' => $prefix,
                        'path'   => rtrim($path, '\\/'),
                    ]; 

                    continue; 
                }               
            }

            $namespaces[] = [
                'prefix' => $prefix,
                'path'   => rtrim($path, '\\/').DIRECTORY_SEPARATOR,
            ]; 
            
        }
        
        $namespaces[] = $system;
        
        return $namespaces;
    }
    
    /**
     * Checks the app directory to see if the file can be found.
     * Only for use with filenames that DO NOT include namespacing.
     * 
     * @param  string  $file
     * @param string|null  $directory
     * 
     * @return string|bool The path to the file, or false if not found.
     */
    protected function legacyLocate(string $file, ?string $directory = null)
    {
        $path = APP_PATH.(empty($directory) ? $file : $directory.DIRECTORY_SEPARATOR.$file);
        $path = realpath($path) ?: $path;
            
        
        if (is_file($path)) {
            return $path;
        }

        return false;
    }
}