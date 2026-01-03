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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Filesystem;

use FilesystemIterator;
use Syscodes\Components\Filesystem\Exceptions\FileException;
use Syscodes\Components\Filesystem\Exceptions\FileNotFoundException;
use Syscodes\Components\Filesystem\Exceptions\FileUnableToMoveException;

/**
 * Provides basic utility to manipulate the file system.
 */
class Filesystem
{
	/**
	 * Append given data string to this file.
	 *
	 * @param  string  $path
	 * @param  mixed  $data
	 *
	 * @return int|false
	 */
	public function append(string $path, mixed $data): int|false
	{
		return file_put_contents($path, $data, FILE_APPEND);
	}

	/**
	 * Copy a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 * 
	 * @return bool
	 */
	public function copy(string $path, string $target): bool
	{
		return copy($path, $target);
	}
	
	/**
	 * Get the contents of a file as decoded JSON.
	 * 
	 * @param  string  $path
	 * @param  int  $flags
	 * @param  bool  $lock
	 * 
	 * @return array
	 * 
	 * @throws \Syscodes\Components\Contracts\Filesystem\FileNotFoundException
	 */
	public function json(string $path, int $flags = 0, bool $lock = false)
	{
		return json_decode($this->get($path, $lock), true, 512, $flags);
	}

	/**
	 * Get the contents of a file.
	 *
	 * @param  string  $path
	 * @param  bool  $lock 
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException
	 */
	public function get(string $path, bool $lock = false): string
	{
		if ($this->isFile($path)) {
			return $lock ? $this->read($path) : file_get_contents($path);
		}

		throw new FileNotFoundException($path);
	}

	/**
	 * Get contents of a file with shared access.
	 *
	 * @param  string  $path  
	 *
	 * @return string
	 */
	public function read(string $path): string
	{
		$contents = '';

		$handle = fopen($path, 'rb');
		
		if ($handle) {
			try {
				if (flock($handle, LOCK_SH)) {
					$this->clearStatCache($path);

					$contents = fread($handle, $this->size($path) ?: 1);
					
					while ( ! feof($handle)) {
						$contents .= fgets($handle, 4096);
					}

					flock($handle, LOCK_UN);
				}
			} finally {
				fclose($handle);
			}
		}

		return trim($contents);
	}

	/**
	 * Creates the file.
	 * 
	 * @param  string  $path
	 * 
	 * @return bool
	 */
	public function create(string $path): bool
	{
		if (($this->isDirectory($path)) && ($this->isWritable($path)) || ( ! $this->exists($path))) {
			if (touch($path)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a file exists.
	 *
	 * @param  string  $path
	 *
	 * @return bool
	 */
	public function exists(string $path): bool
	{
		$this->clearStatCache($path);

		return file_exists($path);
	}

	/**
	 * Clear PHP's internal stat cache.
	 *
	 * @param  string  $path
	 * @param  bool  $all  Clear all cache or not
	 *
	 * @return void
	 */
	public function clearStatCache(string $path, bool $all = false): void
	{
		if ($all === false) {
			clearstatcache(false, $path);
		}

		clearstatcache();
	}

	/**
	 * Get the returned value of a file.
	 * 
	 * @param  string  $path
	 * @param  array  $data
	 * 
	 * @return mixed
	 * 
	 * @throws \Syscodes\Filesystem\Exceptions\FileNotFoundException
	 */
	public function getRequire(string $path, array $data = [])
	{
		if ($this->isFile($path)) {
			$__path = $path;
			$__data = $data;

			return (static function () use ($__path, $__data) {
				extract($__data, EXTR_SKIP);

				return require $__path;
			})();
		}

		throw new FileNotFoundException($path);
	}

	/**
	 * Require the given file once.
	 * 
	 * @param  string  $path
	 * @param  array  $data
	 * 
	 * @return mixed
	 * 
	 * @throws \Syscodes\Filesystem\Exceptions\FileNotFoundException
	 */
	public function getRequireOnce(string $path, array $data = [])
	{
		if ($this->isFile($path)) {
			$__path = $path;
			$__data = $data;

			return (static function () use ($__path, $__data) {
				extract($__data, EXTR_SKIP);

				return require_once $__path;
			})();
		}

		throw new FileNotFoundException($path);
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
	public function size(string $path, string $unit = 'b'): int|null
	{
		if ( ! $this->exists($path)) {
			$size = filesize($path);

			return match (strtolower($unit)) {
				'kb' => number_format($size / 1024, 3),
				'mb' => number_format(($size / 1024) / 1024, 3),
				default => $size,
			};
		}

		return null;
	}
	
	/**
	 * Returns the file's group.
	 *
	 * @param  string  $path
	 * 
	 * @return int|bool  The file group, or false in case of an error
	 */
	public function group(string $path)
	{
		if ( ! $this->exists($path)) {
			return filegroup($path);
		}

		return false;
	}
	
	/**
	 * Returns true if the file is executable.
	 *
	 * @param  string  $path
	 * 
	 * @return bool  True if file is executable, false otherwise
	 */
	public function exec(string $path): bool
	{
		return is_executable($path);
	}

	/**
	 * Determine if the given path is a directory.
	 *
	 * @param  string  $directory
	 *
	 * @return bool
	 */
	public function isDirectory(string $directory): bool
	{
		return is_dir($directory);
	}

	/**
	 * Determine if the given path is a file.
	 *
	 * @param  string  $file
	 *
	 * @return bool
	 */
	public function isFile(string $file): bool
	{
		return is_file($file);
	}

	/**
	 * Determine if the given path is writable.
	 * 
	 * @param  string  $path
	 * 
	 * @return bool
	 */
	public function isWritable(string $path): bool
	{
		return is_writable($path);
	}

	/**
	 * Returns if true the file is readable.
	 *
	 * @param  string  $path
	 * 
	 * @return bool  True if file is readable, false otherwise
	 */
	public function isReadable(string $path): bool
	{
		return is_readable($path);
	}

	/**
	 * Returns last access time.
	 *
	 * @param  string  $path
	 * 
	 * @return int|bool  Timestamp of last access time, or false in case of an error
	 */
	public function lastAccess(string $path)
	{
		if ( ! $this->exists($path)) {
			return fileatime($path);
		}

		return false;
	}

	/**
	 * Returns last modified time.
	 *
	 * @param  string  $path
	 * 
	 * @return int|bool  Timestamp of last modified time, or false in case of an error
	 */
	public function lastModified(string $path)
	{
		if ( ! $this->exists($path)) {
			return filemtime($path);
		}

		return false;		
	}

	/**
	 * Get all of the directories within a given directory.
	 * 
	 * @param  string  $directory
	 * 
	 * @return array
	 */
	public function directories(string $directory): array
	{
		$directories = [];

		$iterators = new FilesystemIterator($directory);

		foreach ($iterators as $iterator) {
			$directories[] = trim($iterator->getPathname(), '/').'/';
		}

		return $directories;
	}

	/**
	 * Delete the file at a given path.
	 * 
	 * @param  string  $paths
	 * 
	 * @return bool
	 */
	public function delete(string $paths): bool
	{
		$paths = is_array($paths) ? $paths : func_get_args();

		$success = true;

		foreach ($paths as $path) {
			if ( ! @unlink($path)) $success = false;
		}

		return $success;
	}
	
	/**
	 * Get the hash of the file at the given path.
	 * 
	 * @param  string  $path
	 * @param  string  $algorithm
	 * 
	 * @return string
	 */
	public function hash(string $path, string $algorithm = 'md5'): string
	{
		return hash_file($algorithm, $path);
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
	public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
	{
		if ($force) {
			return @mkdir($path, $mode, $recursive);
		}

		mkdir($path, $mode, $recursive);
	}

	/**
	 * Copy a directory from one location to another.
	 * 
	 * @param  string  $directory
	 * @param  string  $destination
	 * @param  int|null  $options  
	 * 
	 * @return bool
	 */
	public function copyDirectory(string $directory, string $destination, ?int $options = null): bool
	{
		if ( ! $this->isDirectory($directory)) return false;

		$options = $options ?: FilesystemIterator::SKIP_DOTS;
		
		// If the destination directory does not actually exist, we will go ahead and
		// create it recursively, which just gets the destination prepared to copy
		// the files over. Once we make the directory we'll proceed the copying.
		if ( ! $this->isdirectory($destination)) {
			$this->makeDirectory($destination, 0777, true);
		}

		$iterators = new FilesystemIterator($directory, $options);

		foreach ($iterators as $iterator) {
			$target = $destination.DIRECTORY_SEPARATOR.$iterator->getBasename();
			
			// As we spin through items, we will check to see if the current file is actually
			// a directory or a file. When it is actually a directory we will need to call
			// back into this function recursively to keep copying these nested folders.
			if ($iterator->isDir()) {
				if ( ! $this->copyDirectory($iterator->getPathname(), $target, $options)) return false;
			}
			// If the current items is just a regular file, we will just copy this to the new
			// location and keep looping. If for some reason the copy fails we'll bail out
			// and return false, so the developer is aware that the copy process failed.
			else {
				if ( ! $this->copy($iterator->getPathname(), $target)) return false;
			}
		}

		return true;
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
	public function deleteDirectory(string $directory, bool $keep = false): bool
	{
		if ( ! $this->isDirectory($directory)) return false;

		$iterators = new filesystemIterator($directory);

		foreach ($iterators as $iterator) {
			// If the item is a directory, we can just recurse into the function and delete 
			// that sub-directory otherwise we'll just delete the file and keep iterating 
			// through each file until the directory is cleaned.
			if ($iterator->isDir() && ! $iterator->isLink()) {
				$this->deleteDirectory($iterator->getPathname());
			}
			// If the item is just a file, we can go ahead and delete it since we're
			// just looping through and waxing all of the files in this directory
			// and calling directories recursively, so we delete the real path.
			else {
				$this->delete($iterator->getPathname());
			}
		}

		if ( ! $keep) @rmdir($directory);

		return true;
	}

	/**
	 * Empty the specified directory of all files and folders.
	 * 
	 * 
	 * @param  string  $directory
	 * 
	 * @return bool
	 */
	public function cleanDirectory(string $directory): bool
	{
		return $this->deleteDirectory($directory, true);
	}

	/**
	 * Moves a file to a new location.
	 * 
	 * @param  string  $from
	 * @param  string  $to
	 * @param  bool  $overwrite  
	 * 
	 * @return bool
	 */
	public function moveDirectory(string $from, string $to, bool $overwrite = false)
	{
		if ($overwrite && $this->isDirectory($to) && ! $this->deleteDirectory($to)) return false;

		if (false === @rename($from, $to)) {
			$error = error_get_last();

			throw new FileUnableToMoveException($from, $to, strip_tags($error['message']));
		}

		$this->perms($to, 0777 & ~umask());
	}

	/**
	 * Attempts to determine the file extension based on the trusted
	 * getType() method. If the mime type is unknown, will return null.
	 * 
	 * @param  string  $path
	 * 
	 * @return string|null
	 */
	public function guessExtension(string $path)
	{
		return FileMimeType::guessExtensionFromType($this->getMimeType($path));
	}

	/**
	 * Retrieve the media type of the file. 
	 * 
	 * @param  string  $path
	 * 
	 * @return string|null
	 */
	public function getMimeType(string $path)
	{
		return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
	}

	/**
	 * Move a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 *
	 * @return bool
	 */
	public function move(string $path, string $target): bool
	{
		if ($this->exists($path)) {
			return rename($path, $target);
		}

		return false;
	}

	/**
	 * Extract the file name from a file path.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	public function name(string $path)
	{
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * Extract the trailing name component from a file path.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	public function basename(string $path)
	{
		return pathinfo($path, PATHINFO_BASENAME);
	}

	/**
	 * Extract the parent directory from a file path.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	public function dirname(string $path)
	{
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * Extract the file extension from a file path.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	public function extension(string $path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 *  Find path names matching a given pattern.
	 * 
	 * @param  string  $pattern
	 * @param  int  $flags  (0 by default)
	 * 
	 * @return array|bool
	 */
	public function glob(string $pattern, int $flags = 0): array|bool
	{
		return glob($pattern, $flags);
	}

	/**
	 * Returns the file's owner.
	 *
	 * @param  string  $path
	 * 
	 * @return int|bool  The file owner, or false in case of an error
	 */
	public function owner(string $path)
	{
		if ($this->exists($path)) {
			return fileowner($path);
		}

		return false;
	}

	/**
	 * Returns the "chmod" (permissions) of the file.
	 *
	 * @param  string  $path
	 * @param  int|null  $mode  
	 * 
	 * @return mixed  Permissions for the file, or false in case of an error
	 */
	public function perms($path, $mode = null): mixed
	{
		if ($mode) {
			chmod($path, $mode);
		}

		return substr(sprintf('%o', fileperms($path)), -4);
	}

	/**
	 * Prepend to a file.
	 * 
	 * @param  string  $path
	 * @param  string  $contents
	 * 
	 * @return int|bool
	 */
	public function prepend(string $path, string $contents): int|bool
	{
		if ($this->exists($path)) {
			$this->put($path, $contents.$this->get($path));
		}

		return $this->put($path, $contents);
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
	public function put(string $path, string $contents, bool $lock = false): int|bool
	{
		return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
	}

	/**
	 * Get the file type of a given file.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	public function type(string $path): string
	{
		return filetype($path);
	}
	
	/**
	 * Write the contents of a file, replacing it atomically if it already exists.
	 * 
	 * @param  string  $path
	 * @param  string  $contents
	 * @param  int|null  $mode
	 * 
	 * @return void
	 */
	public function replace(string $path, string $contents, ?int $mode = null): void
	{
		$this->clearstatcache($path);
		
		$path = realpath($path) ?: $path;
		
		$tempPath = tempnam(dirname($path), basename($path));
		
		// Permission files
		if ( ! is_null($mode)) {
			$this->perms($tempPath, $mode);
		} else {
			$this->perms($tempPath, 0777 - umask());
		}
		
		$this->put($tempPath, $contents);
		
		$this->move($tempPath, $path);
    }
	
	/**
	 * Replace a given string within a given file.
	 * 
	 * @param  array|string  $search
	 * @param  array|string  $replace
	 * @param  string  $path
	 * 
	 * @return void
	 */
	public function replaceInFile(array|string $search, array|string $replace, string $path): void
	{
		file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
	}

	/**
	 * Searches for a given text and replaces the text if found.
	 *
	 * @param  string  $path
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  bool|null  $lock
	 *
	 * @return bool
	 */
	public function replaceText(string $path, string $search, string $replace, ?bool $lock = null): bool
	{
		if ( ! $handle = fopen($path, 'r+')) {
			return false;
		}

		if ($lock !== null) {
			if (flock($handle, LOCK_EX) === false) {
				return false;
			}
		}

		$replaced = $this->write($path, str_replace($search, $replace, $this->get($path)), $lock);

		if ($lock !== null) {
			flock($handle, LOCK_UN);
		}

		fclose($handle);

		return $replaced;
	}	

	/**
	 * Write given content to this file.
	 *
	 * @param  string  $path
	 * @param  string  $contents  Content to write to this File
	 * @param  bool|null  $lock
	 *
	 * @return bool
	 */
	public function write(string $path, string $contents, ?bool $lock = null): bool
	{
		$success = false;

		if ( ! $handle = fopen($path, 'w')) {
			return false;
		}

		if ($lock !== null) {
			if (flock($handle, LOCK_EX) === false) {
				return false;
			}
		}

		if (fwrite($handle, $contents) !== false) {
			$success = true;
		}

		if ($lock !== null) {
			flock($handle, LOCK_UN);
		}

		fclose($handle);

		return $success;
	}
}