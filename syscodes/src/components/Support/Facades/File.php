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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscodes\Support\Facades;

/**
 * Initialize the File class facade.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 * 
 * @method static bool append(string $path, string $data, bool $force = false)
 * @method static bool copy(string $path, string $target)
 * @method static string get(string $path, bool $lock = false, bool $force = false)
 * @method static string read(string $path, bool $force = false)
 * @method static bool open(string $path, string $mode, $force = false)
 * @method static bool create(string $path)
 * @method static bool exists(string $path)
 * @method static void clearStatCache(string $path, bool $all = false)
 * @method static int getSize($path, $unit = 'b')
 * @method static string group(string $path)
 * @method static string exec(string $path)
 * @method static isDirectory(string $directory)
 * @method static bool isFile(string $file)
 * @method static bool isWritable(string $path)
 * @method static bool isReadable(string $path)
 * @method static int|bool lastAccess(string $path)
 * @method static int|bool lastModified(string $path)
 * @method static array directories(array $directory)
 * @method static bool delete(string $paths)
 * @method static bool makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
 * @method static bool copyDirectory(string $directory, string $destination, $options = null)
 * @method static bool deleteDirectory(string $directory, bool $keep = false)
 * @method static bool cleanDirectory(string $directory)
 * @method static bool moveDirectory(string $from, string $to, bool $overwrite = false)
 * @method static string uessExtension(string $path)
 * @method static string getMimeType(string $path)
 * @method static bool move(string $path, string $target)
 * @method static string name(string $path)
 * @method static string basename(string $path)
 * @method static string dirname(string $path)
 * @method static string extension(string $path)
 * @method static array glob(string $pattern, bool $flags = 0)
 * @method static int|bool owner(string $path)
 * @method static mixed perms(string $path, int $mode = null)
 * @method static int prepend(string $path, string $data)
 * @method static int put(string $path, string $contents, bool $lock = false)
 * @method static string type(string $path)
 * @method static bool replaceText(string $path, string $search, string $replace)
 * @method static bool close()
 * @method static bool write(string $path, string $data, bool $force = false)
 * 
 * @see \Syscodes\Filesystem\Filesystem
 */
class File extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'files';
    }
}