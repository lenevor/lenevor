<?php 

namespace Syscode\Filesystem;

use FilesystemIterator;
use Syscode\Filesystem\Exceptions\{
	FileException,
	FileNotFoundException
};

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
 * @since       0.1.0
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
	 * The options for active flags.
	 *
	 * @var string $options
	 */
	protected $options;

	/**
	 * The path of file.
	 *
	 * @var string $path
	 */
	protected $path;

	/**
	 * The files size in bytes.
	 *
	 * @var float $size
	 */
	protected $size;

	/**
	 * Append given data string to this file.
	 *
	 * @param  string  $data
	 * @param  bool    $force
	 *
	 * @return bool
	 */
	public function append($data, $force = false)
	{
		return $this->write($data, 'a', $force);
	}

	/**
	 * Constructor with an optional verification that the path is 
	 * really a file.
	 *
	 * @param  string  $path
	 * @param  string  $options
	 * @param  bool    $check
	 * 
	 * @return string
	 *
	 * @throws FileNotFoundException
	 */
	public function __construct($path, $options = null, $check = false)
	{
		if ($check && ! $this->isFile($path))
		{
			throw new FileNotFoundException();
		}

		$this->path    = $path;
		$this->options = $options;
	}

	/**
	 * Clear PHP's internal stat cache.
	 *
	 * @param  bool  $all Clear all cache or not
	 *
	 * @return void
	 */
	public function clearStatCache($all = false)
	{
		if ($all === false) 
		{
			clearstatcache(true, $this->path);
		}

		clearstatcache();
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
	 * Creates the file.
	 *
	 * @return bool
	 */
	public function create()
	{
		if ($this->isDirectory($this->path) && $this->isWritable() && ! $this->exists())
		{
			if (touch($this->path))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if the file is executable.
	 *
	 * @return bool  True if file is executable, false otherwise
	 */
	public function exec()
	{
		return is_executable($this->path);
	}

	/**
	 * Determine if a file exists.
	 *
	 * @param  string|null  $path
	 *
	 * @return bool
	 */
	public function exists($path = null)
	{
		$this->clearStatCache();

		if ($path !== null)
		{
			$this->path = $path;
		}

		return file_exists($this->path);
	}

	public function delete()
	{
		
	}

	/**
	 * Get the contents of a file.
	 *
	 * @param  bool  $lock
	 *
	 * @return string
	 *
	 * @throws FileNotFoundException
	 */
	public function get($mode = 'r', $force = false, $lock = false)
	{
		if ($this->isFile($this->path))
		{
			return $lock ? $this->read($mode, $force) : file_get_contents($this->path);
		}

		throw new FileNotFoundException("File does not exist at path [{$this->path}]");
	}

	/**
	 * Retrieve the file size.
	 *
	 * Implementations SHOULD return the value stored in the "size" key of
	 * the file in the $_FILES array if available, as PHP calculates this
	 * based on the actual size transmitted.
	 *
	 * @param  string    $unit 
	 * 
	 * @return int|null  The file size in bytes or null if unknown
	 */
	public function getSize($unit = 'b')
	{
		if ($this->exists())
		{
			if (is_null($this->size))
			{
				$this->size = filesize($this->path);
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
	 * @return int|bool  The file group, or false in case of an error
	 */
	public function group()
	{
		if ($this->exists())
		{
			return filegroup($this->path);
		}

		return false;
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
	 * @return bool
	 */
	public function isWritable()
	{
		return is_writable($this->path);
	}

	/**
	 * Returns last access time.
	 *
	 * @return int|bool  Timestamp of last access time, or false in case of an error
	 */
	public function lastAccess()
	{
		if ($this->exists())
		{
			return fileatime($this->path);
		}

		return false;
	}

	/**
	 * Returns last modified time.
	 *
	 * @return int|bool  Timestamp of last modified time, or false in case of an error
	 */
	public function lastChange()
	{
		if ($this->exists())
		{
			return filemtime($this->path);
		}

		return false;
	}

	/**
	 * Create a directory.
	 *
	 * @param  string  $path
	 * @param  int     $mode
	 * @param  bool    $recursive
	 * @param  bool    $force
	 *
	 * @return bool
	 * 
	 * @throws FileException
	 */
	public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
	{
		if ($force)
		{
			if (($result = @mkdir($path, $mode, $recursive) === false))
			{
				throw new FileException("The directory [ {$path} ] could not be created");
			}
		}

		$result = mkdir($path, $mode, $recursive);

		chmod($path, $mode);

		return $result;
	}

	/**
	 * Move a file to a new location.
	 *
	 * @param  string  $target
	 *
	 * @return bool
	 */
	public function rename($target)
	{
		if ($this->exists()) 
		{
			return rename($this->path, $target);
		}
	}

	/**
	 * Opens the current file with a given $mode.
	 *
	 * @param  string  $mode   A valid 'fopen' mode string (r|w|a ...)
	 * @param  bool    $force  
	 *
	 * @return bool
	 */
	public function open($mode = 'r', $force = false)
	{
		if ( ! $force && is_resource($this->handler))
		{
			return true;
		}

		if ($this->exists() === false)
		{
			if ($this->create() === false)
			{
				return false;
			}
		}

		$this->handler = fopen($this->path, $mode);

		return is_resource($this->handler);
	}

	/**
	 * Returns the file's owner.
	 *
	 * @return int|bool  The file owner, or false in case of an error
	 */
	public function owner()
	{
		if ($this->exists())
		{
			return fileowner($this->path);
		}

		return false;
	}

	/**
	 * Returns the "chmod" (permissions) of the file.
	 *
	 * @return string|bool  Permissions for the file, or false in case of an error
	 */
	public function perms()
	{
		if ($this->exists())
		{
			return substr(sprintf('%o', fileperms($this->path)), -4);
		}

		return false;
	}

	/**
	 * Write the content of a file.
	 *
	 * @param  string  $contents
	 * @param  bool    $lock
	 *
	 * @return int
	 */
	public function put($contents, $lock = false)
	{
		return file_put_contents($this->path, $contents, $lock ? LOCK_EX : 0);
	}

	/**
	 * Get contents of a file with shared access.
	 *
	 * @param  string  $path   A `fread` compatible mode
	 * @param  bool    $force  
	 *
	 * @return string
	 */
	public function read($mode, $force = false)
	{
		$contents = '';

		$this->open($mode, $force);
		
		if ($this->handler) 
		{
			try
			{
				if (flock($this->handler, LOCK_SH))
				{
					$this->clearStatCache();

					$contents = fread($this->handler, $this->getSize() ?: 1);
					
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
	 * Returns if true the file is readable.
	 *
	 * @return bool  True if file is readable, false otherwise
	 */
	public function readable()
	{
		return is_readable($this->path);
	}

	/**
	 * Searches for a given text and replaces the text if found.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 *
	 * @return bool
	 */
	public function replaceText($search, $replace)
	{
		if ( ! $this->open('r+'))
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

		$replaced = $this->write(str_replace($search, $replace, $this->get()), 'w', true);

		if ($this->lock !== null)
		{
			flock($this->handler, LOCK_UN);
		}

		$this->close();

		return $replaced;
	}

	/**
	 * Write given data to this file.
	 *
	 * @param  string  $data   Data to write to this File
	 * @param  string  $mode   Mode of writing
	 * @param  bool    $force  The file to open
	 *
	 * @return bool
	 */
	public function write($data, $mode = 'w', $force = false)
	{
		$success = false;

		if ($this->open($mode, $force) === true)
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