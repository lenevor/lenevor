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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscode\Cache\Store;

use Syscode\Contracts\Store;
use Syscode\Cache\Types\CacheKey;
use Syscode\Filesystem\Filesytem;

/**
 * File system cache handler.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class FileStore implements Store
{
    /**
     * The extension file called '.cache'.
     * 
     * @var string $extension
     */
    protected $extension = '.cache';

    /**
     * The FileSystem instance.
     * 
     * @var string $files
     */
    protected $files;

    /**
     * The File cache directory.
     * 
     * @var string $directory
     */
    protected $directory;

    /**
     * Constructor. Create a new file cache store instance.
     * 
     * @param   \Syscode\FileSystem\FileSystem  $files
     * @param   string                          $directory
     * 
     * @return  void
     */
    public function __construct(FileSystem $files, $directory)
    {
        $this->files     = $files;
        $this->directory = $directory;
    }

    /**
     * Retrieve an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get($key)
    {
        return $this->getPayLoad($key)['data'] ?? null;
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return array
     */
    protected function getPayLoad($key)
    {
        $path = $this->path($key);
    }

    /**
     * Create the file cache directory if necessary.
     * 
     * @param  string  $path
     * 
     * @return void
     */
    public function createCacheDirectory($path)
    {
        if ( ! $this->files->exists(dirname($path)))
        {
            $this->files->makeDirectory(dirname($path), DIR_READ_WRITE_MODE, true, true);
        }
    }

    /**
     * Get the directory cache.
     * 
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
    
    /**
     * Get the filesystem instance.
     * 
     * @return string
     */
    public function getFileSystem()
    {
        return $this->files;
    }

     /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }
   
    /**
     * Gets the path for a given key.
     * 
     * @param  string  $key
     * 
     * @return string
     */
    protected function path($key)
    {
        $key = new CacheKey($key);

        return $this->directory.DIRECTORY_SEPARATOR.$key.$this->extension;
    }

    /**
     * Sets the extension file cache.
     * 
     * @param  string  $extension
     * 
     * @return string
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }
}