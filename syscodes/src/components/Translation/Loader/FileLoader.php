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

namespace Syscodes\Components\Translation\Loader;

use RuntimeException;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Contracts\Translation\Loader as LoaderContract;

/**
 * Automatically loads the messages according to the type of 
 * file in php format.
 */
class FileLoader implements LoaderContract
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Components\Filesystem\Filesystem $files
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
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  array|string  $path
     * 
     * @return void
     */
    public function __construct(Filesystem $files, array|string $path)
    {
        $this->files = $files;
        $this->path  = is_string($path) ? [$path] : $path;
    }

    /**
     * Load the messages for the given locale.
     * 
     * @param  string  $locale
     * @param  string  $group
     * 
     * @return array
     */
    public function load($locale, $group): array
    {
        if ($group === '*') {
            return $this->loadJsonPaths($locale);
        }

        return $this->loadFilePaths($locale, $group);
    }

    /**
     * Load a locale from a given file path.
     * 
     * @param  string  $locale
     * @param  string  $group
     * 
     * @return array
     */
    protected function loadFilePaths($locale, $group): array
    {
        return collect($this->path)
            ->reduce(function ($output, $path) use ($locale, $group) {
                $slash = DIRECTORY_SEPARATOR;
        
                if ($this->files->exists($fullPath = "{$path}$slash{$locale}$slash{$group}.php")) {
                    $output = array_replace_recursive($output, $this->files->getRequire($fullPath));
                }
        
                return $output;
            }, []);
    }

    /**
     * Load a locale from a given Json file path.
     * 
     * @param  string  $locale
     * 
     * @return array
     */
    protected function loadJsonPaths($locale): array
    {
        return collect($this->path)
            ->reduce(function ($output, $path) use ($locale) {
                $slash = DIRECTORY_SEPARATOR;

                if ($this->files->exists($fullPath = "{$path}$slash{$locale}.json")) {
                    $decoded = json_decode($this->files->get($fullPath), true);
                    
                    if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                        throw new RuntimeException("Translation file [{$fullPath}] contains an invalid JSON structure");
                    }

                    $output = array_merge($output, $decoded);
                }

                return $output;
            }, []);
    }
}