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

 namespace Syscodes\Components\Filesystem;

use Syscodes\Components\Contracts\Filesystem\Filesystem;

/**
 * Allows manipulate the file system depending on the file adapter type.
 */
class FilesystemAdapter implements Filesystem
{
    /**
     * Get the config to file system.
     * 
     * @var array $config
     */
    protected $config = [];

    /**
     * Get the driver of file system.
     * 
     * @var string|object $driver
     */
    protected $driver;

    /**
     * Constructor. Create a new FilesystemAdapter instance.
     * 
     * @param  string|object  $driver
     * @param  array  $config
     * 
     * @return void
     */
    public function __construct($driver, array $config = [])
    {
        $this->driver = $driver;
        $this->config = $config;
    }

    /**
	 * Append given data string to this file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 *
	 * @return bool
	 */
	public function append($path, $data)
    {
        return $this->driver->append($path, $data);
    }

    /**
	 * Copy a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 * 
	 * @return bool
	 */
	public function copy($path, $target): bool
    {
		return $this->driver->copy($path, $target);
    }

    /**
	 * Get the contents of a file.
	 *
	 * @param  string  $path
	 * @param  bool  $lock  
	 * @param  bool  $force  
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException
	 */
	public function get($path, $lock = false, $force = false): string
    {
		return $this->driver->get($path, $lock, $force);
    }

    /**
	 * Get contents of a file with shared access.
	 *
	 * @param  string  $path
	 * @param  bool  $force  
	 *
	 * @return string
	 */
	public function read($path, $force = false): string
    {
		return $this->driver->read($path, $force);
    }

    /**
	 * Creates the file.
	 * 
	 * @param  string  $path
	 * 
	 * @return bool
	 */
	public function create($path): bool
    {
		return $this->driver->create($path);
    }

    /**
	 * Determine if a file exists.
	 *
	 * @param  string  $path
	 *
	 * @return bool
	 */
	public function exists($path): bool
    {
		return $this->driver->exists($path);
    }

    /**
	 * Retrieve the file size.
	 *
	 * Implementations SHOULD return the value stored in the "size" key of
	 * the file in the $_FILES array if available, as PHP calculates this
	 * based on the actual size transmitted.
	 *
	 * @param  string  $path
	 * @param  string  $unit
	 * 
	 * @return int|null  The file size in bytes or null if unknown
	 */
	public function size($path, $unit = 'b'): int|null
    {
		return $this->driver->size($path, $unit);
    }

    /**
	 * Get all of the directories within a given directory.
	 * 
	 * @param  string  $directory
	 * 
	 * @return array
	 */
	public function directories($directory): array
    {
		return $this->driver->directories($directory);
    }

    /**
	 * Delete the file at a given path.
	 * 
	 * @param  string  $paths
	 * 
	 * @return bool
	 */
	public function delete($paths): bool
    {
		return $this->driver->delete($paths);
    }

    /**
	 * Create a directory.
	 *
	 * @param  string  $path
	 * @param  int  $mode
	 * @param  bool  $recursive
	 * @param  bool  $force
	 *
	 * @return bool
	 * 
	 * @throws FileException
	 */
	public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false): bool
    {
		return $this->driver->makeDirectory($path, $mode, $recursive, $force);
    }

    /**
	 * Recursively delete a directory and optionally you can keep 
	 * the directory if you wish.
	 * 
	 * @param  string  $directory
	 * @param  bool  $keep
	 * 
	 * @return bool
	 */
	public function deleteDirectory($directory, $keep = false): bool
    {
		return $this->driver->deleteDirectory($directory, $keep);
    }

    /**
	 * Move a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 *
	 * @return bool
	 */
	public function move($path, $target): bool
    {
		return $this->driver->move($path, $target);
    }

    /**
	 * Prepend to a file.
	 * 
	 * @param  string  $path
	 * @param  string  $data
	 * 
	 * @return int
	 */
	public function prepend($path, $data): int
    {
		return $this->driver->prepend($path, $data);
    }

    /**
	 * Write the content of a file.
	 *
	 * @param  string  $path
	 * @param  string  $contents
	 * @param  bool  $lock  
	 *
	 * @return int|bool
	 */
	public function put($path, $contents, $lock = false): int|bool
    {
		return $this->driver->put($path, $contents, $lock);
    }

    /**
	 * Write given data to this file.
	 *
	 * @param  string  $path
	 * @param  string  $data  Data to write to this File
	 * @param  bool  $force  The file to open
	 *
	 * @return bool
	 */
	public function write($path, $data, $force = false): bool
    {
		return $this->driver->write($path, $data, $force);
    }
}