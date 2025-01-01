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

namespace Syscodes\Components\View\Transpilers;

use InvalidArgumentException;
use Syscodes\Components\Filesystem\Filesystem;

/**
 * Allows transpilation of view file path.
 */
abstract class Transpiler
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Components\Filesystem\Filesytem $files
     */
    protected $files;

    /**
     * Get the cache path for the transpiled views.
     * 
     * @var string $cachePath
     */
    protected $cachePath;

    /**
     * Constructor. Create a new Transpiler instance.
     * 
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  string  $cachePath
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(Filesystem $files, $cachePath)
    {
        if ( ! $cachePath) {
            throw new InvalidArgumentException('Please verify that the cache path is valid.');
        }

        $this->files     = $files;
        $this->cachePath = $cachePath;
    }

    /**
     * Get the path to the transpiled version of a view.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    public function getTranspilePath($path): string
    {
        return $this->cachePath.DIRECTORY_SEPARATOR.sha1($path).'.php';
    }

    /**
     * Determine if the view at the given view is expired.
     * 
     * @param  string  $path
     * 
     * @return bool
     */
    public function isExpired($path): bool
    {
        $compiled = $this->getTranspilePath($path);

        if ( ! $this->files->exists($compiled)) {
            return true;
        }

        return $this->files->lastModified($path) >=
               $this->files->lastModified($compiled);
    }

    /**
     * Create the transpiled file directory if neccesary.
     * 
     * @param  string  $path
     * 
     * @return void
     */
    protected function transpiledDirectoryExists($path): void
    {
        if ( ! $this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }
}

