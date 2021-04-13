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
 * file in php or json format. 
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
    public function load($locale, $group = '*')
    {
        if (isset($group)) {
            return $this->loadFilePaths($locale, $group);
        }

        return $this->loadJsonPaths($locale);
    }

    /**
     * Load a locale from the given JSON file path.
     * 
     * @param  string  $locale
     * 
     * @return array
     * 
     * @throws \RuntimeException
     */
    protected function loadJsonPaths($locale)
    {
        if ($this->files->exists($fullPath = "{$this->path}/{$locale}.json")) {
            $output = json_decode($this->files->get($fullPath), true);

            if (is_null($output) || json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Translation file [{$fullPath}] contains an invalid JSON structure");
            }

            return $output;
        }

        return [];
    }

    /**
     * Load a locale from a given path.
     * 
     * @param  string  $path
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