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

namespace Syscodes\Components\Cache\Store;

use Exception;
use Syscodes\Components\Contracts\Cache\Key;
use Syscodes\Components\Contracts\Cache\Store;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Cache\Concerns\CacheKey;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Cache\Utils\FileCacheRegister;
use Syscodes\Components\Cache\concerns\CacheMultipleKeys;

/**
 * File system cache handler.
 */
class FileStore implements Key, Store
{
    use CacheKey,
        CacheMultipleKeys,
        InteractsWithTime;

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
     * Gets an item from the cache by key.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function get(string $key)
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
    protected function getPayLoad(string $key): array
    {
        $path = $this->path($key);

        try {
            $expires = substr(
                $contents = $this->files->get($path, true), 0, 10
            );
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
    protected function path(string $key): string
    {
        $this->getFixKeyChars($key);

        return $this->directory.DIRECTORY_SEPARATOR.$this->getKeyName().$this->extension;
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
     * Store an item in the cache for a given number of seconds.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $seconds
     * 
     * @return bool
     */
    public function put(string $key, mixed $value, int $seconds): bool
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
    protected function createCacheDirectory(string $path): void
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
     * @return int
     */
    protected function expiration(int $seconds): int
    {
        $time = $this->availableAt($seconds);

        return $seconds === 0 || $time > 9999999999 ? 9999999999 : $time;
    }

    /**
     * Increment the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function increment(string $key, mixed $value = 1): int|bool
    {
        $raw = $this->getPayLoad($key);
        $int = ((int) $raw['data']) + $value;

        $this->put($key, $int, $raw['time'] ?? 0);

        return $int;
    }

    /**
     * Decrement the value of an item in the cache.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return int|bool
     */
    public function decrement(string $key, mixed $value = 1): int|bool
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Deletes a specific item from the cache store.
     * 
     * @param  string  $key
     * 
     * @return mixed
     */
    public function delete(string $key): mixed
    {
        if ($this->files->exists($file = $this->path($key))) {
            return $this->files->delete($file);
        }

        return false;
    }

    /**
     * Stores an item in the cache indefinitely.
     * 
     * @param  string  $key
     * @param  mixed   $value
     * 
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove all items from the cache.
     * 
     * @return bool
     */
    public function flush(): bool
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
     * Gets the cache key prefix.
     *
     * @return string
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