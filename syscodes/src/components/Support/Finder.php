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

namespace Syscodes\Components\Support;

use OutOfBoundsException;
use InvalidArgumentException;

/**
 * Finder allows to find files and directories in all the system.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
            static::$instance = static::render();
        }

        return static::$instance;
    }

    /**
     * Render new Finders.
     *
     * @return Finder
     */
    public static function render()
    {
        return new static();
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
    public static function search(string $file = null, string $directory = null, $extension = 'php')
    {
        return static::instance()->locate($file, $directory, $extension);
    }

    /**
     * Adds a path (or paths) to the search path at a given position.
     *
     * Possible positions:
     *   (null):  Append to the end of the search path
     *   (-1):    Prepend to the start of the search path
     *   (index): The path will get inserted AFTER the given index
     *
     * @param  string|array  $paths  The path to add
     * @param  int  $pos  The position to add the path  
     *
     * @return self
     *
     * @throws \OutOfBoundsException
     */
    public function addPath($paths, $pos = null): self
    {
        if ( ! is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            if ($pos === null) {
                $this->paths[] = $this->prepPath($path);
            } elseif ($pos === -1) {
                array_unshift($this->paths, $this->prepPath($path));
            } else {
                if ($pos > count($this->paths)) {
                    throw new OutOfBoundsException(sprintf("Position %s is out of range", $pos));
                }
                
                array_splice($this->paths, $pos, 0, $this->prepPath($path));
            }
        }

        return $this;
    }

    /**
     * Returns the search for a directory and file with their respective extension.
     *
     * @param  string  $file  The file
     * @param  string  $directory  The directory
     * @param  string  $extension  The file extension  
     *
     * @return bool|string
     *
     * @throws \InvalidArgumentException
     */
    public function locate(?string $file, ?string $directory = null, $extension = 'php')
    {
        $file = $this->getExtension($file, $extension);

        return $file;
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
     * Prepares a path for usage. It ensures that the path has a trailing.
     * Directory Separator.
     *
     * @param  string  $path  The path to prepare
     *
     * @return string
     */
    public function prepPath($path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        return rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    /**
     * Checks the app directory to see if the file can be found.
     * Only for use with filenames that DO NOT include namespacing.
     *
     * @return false|string The path to the file, or false if not found.
     */
    protected function legacyLocate(string $file, ?string $directory = null)
    {
        $path = APP_PATH . (empty($directory) ? $file : $directory . '/' . $file);
        $path = realpath($path) ?: $path;

        if (is_file($path)) {
            return $path;
        }

        return false;
    }
}