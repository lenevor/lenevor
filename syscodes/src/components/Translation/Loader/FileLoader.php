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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Translation\Loader;

use RuntimeException;
use Syscodes\Filesystem\Filesystem;
use Syscodes\Contracts\Translation\Loader as LoaderContract;

/**
 * Automatically loads the messages according to the type of 
 * file in php format. 
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class FileLoader implements LoaderContract
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Filesystem\Filesystem $files
     */
    protected $files;

    /**
     * The default path for the loader.
     * 
     * @var string $path
     */
    protected $path;

    /**
     * Constructor. Create a new File Loader instance.
     * 
     * @param  \Syscodes\Filesystem\Filesystem  $files
     * @param  string  $path
     * 
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->files = $files;
        $this->path  = $path;
    }

    /**
     * Load the messages for the given locale.
     * 
     * @param  string  $locale
     * @param  string  $group
     * 
     * @return array
     */
    public function load($locale, $group)
    {
        return $this->loadFilePaths($locale, $group);
    }

    /**
     * Load a locale from a given path.
     * 
     * @param  string  $locale
     * @param  string  $group
     * 
     * @return array
     */
    protected function loadFilePaths($locale, $group)
    {
        if ($this->files->exists($fullPath = "{$this->path}/{$locale}/{$group}.php")) {
            return $this->files->getRequire($fullPath);
        }

        return [];
    }
}