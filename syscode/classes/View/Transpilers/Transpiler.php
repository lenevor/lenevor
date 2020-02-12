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
 * @since       0.6.0
 */

namespace Syscode\View\Transpilers;

use InvalidArgumentException;
use Syscode\Filesystem\Filesystem;

/**
 * Allows transpilation of view file path.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
abstract class Transpiler
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscode\Filesystem\Filesytem $files
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
     * @param  \Syscode\Filesystem\Filesystem  $files
     * @param  string  $cachePath
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(Filesystem $files, $cachePath)
    {
        if ( ! $cachePath)
        {
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
    public function getTranspilePath($path)
    {
        return $this->cachePath.DIRECTORY_SEPARATOR.sha1($path).'.php';
    }

    /**
     * Determine if the view at the given path is expired.
     * 
     * @param  string  $path
     * 
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getTranspilePath($path);

        if ( ! $this->files->exists($compiled))
        {
            return true;
        }

        return $this->files->lastModified($path) >=
               $this->files->lastModified($compiled);
    }
}

