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

namespace Syscodes\Components\Contracts\Filesystem;

/**
 * Provides basic utility to manipulate the file system.
 */
interface Filesystem
{
    /**
     * The public visibility setting.
     * 
     * @var string
     */
    const VISIBILITY_PUBLIC = 'public';
    
    /**
     * The private visibility setting.
     * 
     * @var string
     */
    const VISIBILITY_PRIVATE = 'private';
    
    /**
	 * Append given data string to this file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 *
	 * @return bool
	 */
	public function append($path, $data);

    /**
	 * Copy a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 * 
	 * @return bool
	 */
	public function copy($path, $target): bool;

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
	public function get($path, $lock = false, $force = false): string;

    /**
	 * Get contents of a file with shared access.
	 *
	 * @param  string  $path
	 * @param  bool  $force  
	 *
	 * @return string
	 */
	public function read($path, $force = false): string;

    /**
	 * Creates the file.
	 * 
	 * @param  string  $path
	 * 
	 * @return bool
	 */
	public function create($path): bool;

    /**
	 * Determine if a file exists.
	 *
	 * @param  string  $path
	 *
	 * @return bool
	 */
	public function exists($path): bool;

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
	public function size($path, $unit = 'b'): int|null;

    /**
	 * Get all of the directories within a given directory.
	 * 
	 * @param  string  $directory
	 * 
	 * @return array
	 */
	public function directories($directory): array;

    /**
	 * Delete the file at a given path.
	 * 
	 * @param  string  $paths
	 * 
	 * @return bool
	 */
	public function delete($paths): bool;

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
	public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false): bool;

    /**
	 * Recursively delete a directory and optionally you can keep 
	 * the directory if you wish.
	 * 
	 * @param  string  $directory
	 * @param  bool  $keep
	 * 
	 * @return bool
	 */
	public function deleteDirectory($directory, $keep = false): bool;

    /**
	 * Move a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 *
	 * @return bool
	 */
	public function move($path, $target): bool;

    /**
	 * Prepend to a file.
	 * 
	 * @param  string  $path
	 * @param  string  $data
	 * 
	 * @return int
	 */
	public function prepend($path, $data): int;

    /**
	 * Write the content of a file.
	 *
	 * @param  string  $path
	 * @param  string  $contents
	 * @param  bool  $lock  
	 *
	 * @return int|bool
	 */
	public function put($path, $contents, $lock = false): int|bool;

    /**
	 * Write given data to this file.
	 *
	 * @param  string  $path
	 * @param  string  $data  Data to write to this File
	 * @param  bool  $force  The file to open
	 *
	 * @return bool
	 */
	public function write($path, $data, $force = false): bool;
}