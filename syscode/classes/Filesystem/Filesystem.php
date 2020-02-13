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
 * @since       0.3.0
 */

namespace Syscode\Filesystem;

use ErrorException;
use FilesystemIterator;
use Syscode\Filesystem\Exceptions\FileException;
use Syscode\Filesystem\Exceptions\FileNotFoundException;
use Syscode\Filesystem\Exceptions\FileUnableToMoveException;

/**
 * Provides basic utility to manipulate the file system.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Filesystem 
{
	/**
	 * Enable locking for file reading and writing.
	 *
	 * @var null|bool $lock
	 */
	public $lock = null;

	/**
	 * Holds the file handler resource if the file is opened.
	 *
	 * @var resource $handler
	 */
	protected $handler;

	/**
	 * The files size in bytes.
	 *
	 * @var float $size
	 */
	protected $size;

	/**
	 * Append given data string to this file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @param  bool  $force
	 *
	 * @return bool
	 */
	public function append($path, $data, $force = false)
	{
		return $this->write($path, $data, 'a', $force);
	}

	/**
	 * Copy a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 * 
	 * @return bool
	 */
	public function copy($path, $target)
	{
		return copy($path, $target);
	}

	/**
	 * Get the contents of a file.
	 *
	 * @param  string  $path
	 * @param  bool  $lock  (false by default)
	 * @param  bool  $force  (false by default)
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException
	 */
	public function get($path, $lock = false, $force = false)
	{
		if ($this->isFile($path))
		{
			return $lock ? $this->read($path, $force) : file_get_contents($path);
		}

		throw new FileNotFoundException($path);
	}

	/**
	 * Get contents of a file with shared access.
	 *
	 * @param  string  $path
	 * @param  bool  $force  
	 *
	 * @return string
	 */
	protected function read($path, $force = false)
	{
		$contents = '';

		$this->open($path, 'rb', $force);
		
		if ($this->handler) 
		{
			try
			{
				if (flock($this->handler, LOCK_SH))
				{
					$this->clearStatCache($path);

					$contents = fread($this->handler, $this->getSize($path) ?: 1);
					
					while ( ! feof($this->handler))
					{
						$contents .= fgets($this->handler, 4096);
					}

					flock($this->handler, LOCK_UN);
				}
			}
			finally
			{
				$this->close();
			}
		}

		return trim($contents);
	}

	/**
	 * Opens the current file with a given $mode.
	 *
	 * @param  string  $path
	 * @param  string  $mode  A valid 'fopen' mode string (r|w|a ...)
	 * @param  bool  $force  
	 *
	 * @return bool
	 */
	public function open($path, $mode, $force = false)
	{
		if ( ! $force && is_resource($this->handler))
		{
			return true;
		}

		if ($this->exists($path) === false)
		{
			if ($this->create($path) === false)
			{
				return false;
			}
		}

		$this->handler = fopen($path, $mode);

		return is_resource($this->handler);
	}

	/**
	 * Creates the file.
	 * 
	 * @param  string  $path
	 * 
	 * @return bool
	 */
	public function create($path)
	{
		if (($this->isDirectory($path)) && ($this->isWritable($path)) || ! $this->exists($path))
		{
			if (touch($path))
			{
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
	public function exists($path)
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
	public function clearStatCache($path, $all = false)
	{
		if ($all === false) 
		{
			clearstatcache(true, $path);
		}

		clearstatcache();
	}

	/**
	 * Retrieve the file size.
	 *
	 * Implementations SHOULD return the value stored in the "size" key of
	 * the file in the $_FILES array if available, as PHP calculates this
	 * based on the actual size transmitted.
	 *
	 * @param  string  $path
	 * @param  string  $unit  ('b' by default)
	 * 
	 * @return int|null  The file size in bytes or null if unknown
	 */
	public function getSize($path, $unit = 'b')
	{
		if ($this->exists($path))
		{
			if (is_null($this->size))
			{
				$this->size = filesize($path);
			}

			switch (strtolower($unit))
			{
				case 'kb':
					return number_format($this->size / 1024, 3);
					break;
				case 'mb':
					return number_format(($this->size / 1024) / 1024, 3);     
					break;
			}

			return $this->size;
		}
	}
	
	/**
	 * Returns the file's group.
	 *
	 * @param  string  $path
	 * 
	 * @return int|bool  The file group, or false in case of an error
	 */
	public function group($path)
	{
		if ($this->exists($path))
		{
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
	public function exec($path)
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
	public function isDirectory($directory)
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
	public function isFile($file)
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
	public function isWritable($path)
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
	public function isReadable($path)
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
	public function lastAccess($path)
	{
		if ($this->exists($path))
		{
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
	public function lastModified($path)
	{
		if ($this->exists($path))
		{
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
	public function directories($directory)
	{
		$directories = [];

		$iterators = new FilesystemIterator($directory);

		foreach ($iterators as $iterator)
		{
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
	public function delete($paths)
	{
		if (is_resource($this->handler))
		{
			fclose($this->handler);
			$this->handler = null;
		}

		$paths = is_array($paths) ? $paths : func_get_args();

		$success = true;

		foreach ($paths as $path)
		{
			try
			{
				if ( ! @unlink($path))
				{
					return $success = false;
				}
			}
			catch (ErrorException $e)
			{
				return $success = false;
			}
		}

		return $success;
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
	public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
	{
		if ($force)
		{
			return @mkdir($path, $mode, $recursive);
		}

		mkdir($path, $mode, $recursive);
	}

	/**
	 * Copy a directory from one location to another.
	 * 
	 * @param  string  $directory
	 * @param  string  $destination
	 * @param  int  $options  (null by default)
	 * 
	 * @return bool
	 */
	public function copyDirectory($directory, $destination, $options = null)
	{
		if ( ! $this->isDirectory($directory)) return false;

		$options = $options ?: FilesystemIterator::SKIP_DOTS;
		
		// If the destination directory does not actually exist, we will go ahead and
		// create it recursively, which just gets the destination prepared to copy
		// the files over. Once we make the directory we'll proceed the copying.
		if ( ! $this->isdirectory($destination))
		{
			$this->makeDirectory($destination, 0777, true);
		}

		$iterators = new FilesystemIterator($directory, $options);

		foreach ($iterators as $iterator)
		{
			$target = $destination.DIRECTORY_SEPARATOR.$iterator->getBasename();
			
			// As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
			if ($iterator->isDir())
			{
				if ( ! $this->copyDirectory($iterator->getPathname(), $target, $options)) return false;
			}
			// If the current items is just a regular file, we will just copy this to the new
			// location and keep looping. If for some reason the copy fails we'll bail out
			// and return false, so the developer is aware that the copy process failed.
			else
			{
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
	public function deleteDirectory($directory, $keep = false)
	{
		if ( ! $this->isDirectory($directory)) return false;

		$iterators = new filesystemIterator($directory);

		foreach ($iterators as $iterator)
		{
			// If the item is a directory, we can just recurse into the function and delete 
			// that sub-directory otherwise we'll just delete the file and keep iterating 
			// through each file until the directory is cleaned.
			if ($iterator->isDir() && ! $iterator->isLink())
			{
				$this->deleteDirectory($iterator->getPathname());
			}
			// If the item is just a file, we can go ahead and delete it since we're
			// just looping through and waxing all of the files in this directory
			// and calling directories recursively, so we delete the real path.
			else
			{
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
	public function cleanDirectory($directory)
	{
		return $this->deleteDirectory($directory, true);
	}

	/**
	 * Moves a file to a new location.
	 * 
	 * @param  string  $from
	 * @param  string  $to
	 * @param  bool  $overwrite  (false by default)
	 * 
	 * @return bool
	 */
	public function moveDirectory($from, $to, $overwrite = false)
	{
		if ($overwrite && $this->isDirectory($to) && ! $this->deleteDirectory($to)) return false;

		if (false === @rename($from, $to))
		{
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
	public function guessExtension($path)
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
	public function getMimeType($path)
	{
		$finfo    = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, $path);

		finfo_close($finfo);

		return $mimeType;
	}

	/**
	 * Move a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 *
	 * @return bool
	 */
	public function move($path, $target)
	{
		if ($this->exists($path)) 
		{
			return rename($path, $target);
		}
	}

	/**
	 * Extract the file name from a file path.
	 * 
	 * @param  string  $path
	 * 
	 * @return string
	 */
	public function name($path)
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
	public function basename($path)
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
	public function dirname($path)
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
	public function extension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 *  Find path names matching a given pattern.
	 * 
	 * @param  string  $pattern
	 * @param  int  $flags  (0 by default)
	 * 
	 * @return array
	 */
	public function glob($pattern, $flags = 0)
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
	public function owner($path)
	{
		if ($this->exists($path))
		{
			return fileowner($path);
		}

		return false;
	}

	/**
	 * Returns the "chmod" (permissions) of the file.
	 *
	 * @param  string  $path
	 * @param  int|null  $mode  (null by default)
	 * 
	 * @return mixed  Permissions for the file, or false in case of an error
	 */
	public function perms($path, $mode = null)
	{
		if ($mode)
		{
			chmod($path, $mode);
		}

		return substr(sprintf('%o', fileperms($path)), -4);
	}

	/**
	 * Prepend to a file.
	 * 
	 * @param  string  $path
	 * @param  string  $data
	 * 
	 * @return int
	 */
	public function prepend($path, $data)
	{
		if ($this->exists($path))
		{
			$this->put($path, $data.$this->get($path));
		}

		return $this->put($path, $data);
	}

	/**
	 * Write the content of a file.
	 *
	 * @param  string  $path
	 * @param  string  $contents
	 * @param  bool  $lock  (false by default)
	 *
	 * @return int
	 */
	public function put($path, $contents, $lock = false)
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
	public function type($path)
	{
		return filetype($path);
	}

	/**
	 * Searches for a given text and replaces the text if found.
	 *
	 * @param  string  $path
	 * @param  string  $search
	 * @param  string  $replace
	 *
	 * @return bool
	 */
	public function replaceText($path, $search, $replace)
	{
		if ( ! $this->open($path, 'r+'))
		{
			return false;
		}

		if ($this->lock !== null)
		{
			if (flock($this->handler, LOCK_EX) === false)
			{
				return false;
			}
		}

		$replaced = $this->write($path, str_replace($search, $replace, $this->get($path)), true);

		if ($this->lock !== null)
		{
			flock($this->handler, LOCK_UN);
		}

		$this->close();

		return $replaced;
	}	

	/**
	 * Closes the current file if it is opened.
	 *
	 * @return bool
	 */
	public function close()
	{
		if ( ! is_resource($this->handler))
		{
			return true;
		}

		return fclose($this->handler);
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
	public function write($path, $data, $force = false)
	{
		$success = false;

		if ($this->open($path, 'w', $force) === true)
		{
			if ($this->lock !== null)
			{
				if (flock($this->handler, LOCK_EX) === false)
				{
					return false;
				}
			}

			if (fwrite($this->handler, $data) !== false)
			{
				$success = true;
			}

			if ($this->lock !== null)
			{
				flock($this->handler, LOCK_UN);
			}
		}

		return $success;
	}
}