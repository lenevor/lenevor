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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Cache\Store;

use Exception;
use Syscodes\Components\Cache\Types\CacheKey;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Cache\Utils\FileCacheRegister;

/**
 * File system cache handler.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class FileStore implements Store
{
    use InteractsWithTime;

    /**
     * The extension file called '.cache'.
     * 
     * @var string $extension
     */
    protected $extension = '.cache';

    /**
     * The FileSystem instance.
     * 
     * @var object $files
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
     * @param   \Syscodes\Components\FileSystem\Filesystem  $files
     * @param   string  $directory
     * 
     * @return  void
     */
    public function __construct(Filesystem $files, $directory)
    {
        $this->files     = $files;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
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
    protected function getPayLoad($key): array
    {
        $path = $this->path($key);

        if ( ! $this->files->exists($path)) {
            return $this->emptyPayLoad();
        }

        try {
            $expires = substr($contents = $this->files->get($path, true), 0, 10);
        } catch (Exception $e) {
            return $this->emptyPayLoad();
        }

        if ($this->currentTime() >= $expires) {
            $this->delete($key);

            return $this->emptyPayLoad();
        }

        try {   
            $data = (new FileCacheRegister)
                    ->unserialize(substr($contents, 10))
                    ->getData();
        } catch (Exception $e) {
            return $this->emptyPayLoad();
        }

        $time = $expires - $this->currentTime();

        return compact('data', 'time');
    }

    /**
     * Gets the path for a given key.
     * 
     * @param  string  $key
     * 
     * @return string
     */
    protected function path($key): string
    {
        $keyname = new CacheKey($key);

        return $this->directory.DIRECTORY_SEPARATOR.$keyname->getKeyName().$this->extension;
    }

    /**
     * Gets a default empty payload for the cache.
     * 
     * @return array
     */
    protected function emptyPayLoad(): array
    {
        return ['data' => null, 'time' => null];
    }

    /**
     * @inheritdoc
     */
    public function put($key, $value, $seconds): bool
    {
        $value = $this->expiration($seconds).(new FileCacheRegister($value))->serialize();

        $this->createCacheDirectory($path = $this->path($key));

        $result = $this->files->put($path, $value, true);

        return $result !== false && $result > 0; 
    }

    /**
     * Create the file cache directory if necessary.
     * 
     * @param  string  $path
     * 
     * @return void
     */
    protected function createCacheDirectory($path): void
    {
        if ( ! $this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), DIR_READ_WRITE_MODE, true, true);
        }
    }

    /**
     * Get the expiration time based on the given seconds.
     * 
     * @param  int  $seconds
     * 
     * @return bool
     */
    protected function expiration($seconds): bool
    {
        $time = $this->availableAt($seconds);

        return $seconds === 0 || $time > 9999999999 ? 9999999999 : $time;
    }

    /**
     * @inheritdoc
     */
    public function increment($key, $value = 1)
    {
        $raw = $this->getPayLoad($key);
        $int = ((int) $raw['data']) + $value;

        $this->put($key, $int, $raw['time'] ?? 0);

        return $int;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if ($this->files->exists($file = $this->path($key))) {
            return $this->files->delete($file);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if ( ! $this->files->isDirectory($this->directory)) {
            return false;
        }

        foreach ($this->files->directories($this->directory) as $directory) {
            if ( ! $this->files->deleteDirectory($directory)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the directory cache.
     * 
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }
    
    /**
     * Get the filesystem instance.
     * 
     * @return object
     */
    public function getFileSystem(): object
    {
        return $this->files;
    }

     /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return '';
    }

    /**
     * Sets the extension file cache.
     * 
     * @param  string  $extension
     * 
     * @return void
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }
}