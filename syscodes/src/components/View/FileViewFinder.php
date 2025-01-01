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

namespace Syscodes\Components\View;

use InvalidArgumentException;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Contracts\View\ViewFinder;
use Syscodes\Components\View\Exceptions\ViewException;

/**
 * Allows location of a view.
 */
class FileViewFinder implements ViewFinder
{
    use Extensions;
    
    /**
     * The filesystem instance.
     * 
     * @var string|object $files
     */
    protected $files;

    /**
     * The namespace to file path hints.
     * 
     * @var array $hints
     */
    protected $hints = [];

    /**
     * The array of active view paths.
     * 
     * @var array $paths
     */
    protected $paths;

    /**
     * The array of views that have been located.
     * 
     * @var array $views
     */
    protected $views = [];

    /**
     * Constructor. Create a new FileViewFinder class instance.
     * 
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * @param  array  $paths
     * @param  array|null  $extensions   
     * 
     * @return void
     */
    public function __construct(Filesystem $files, array $paths, $extensions = null)
    {
        $this->files = $files;
        $this->paths = array_map([$this, 'resolvePath'], $paths);

        if (isset($extensions)) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Get the complete location of the view.
     * 
     * @param  string  $name
     *
     * @return string
     */
    public function find($name): string
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInfo($name = trim($name))) {
            return $this->views[$name] = $this->findNamespacedPaths($name);
        }

        return $this->views[$name] = $this->findPaths($name, $this->paths);
    }

    /**
     * Get the path to a template with a named path.
     * 
     * @param  string  $name
     * 
     * @return string
     */
    protected function findNamespacedPaths($name): string
    {
        [$namespace, $view] = $this->parseNamespaceSegments($name);
        
        return $this->findPaths($view, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a template with a named path.
     * 
     * @param  string  $name
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    protected function parseNamespaceSegments($name): array
    {
        $segments = explode(static::HINT_PATH_DELIMITER, $name);
        
        if (count($segments) !== 2) {
            throw new InvalidArgumentException("View [{$name}] has an invalid name");
        }
        
        if ( ! isset($this->hints[$segments[0]])) {
            throw new InvalidArgumentException("No hint path defined for [{$segments[0]}]");
        }
        
        return $segments;
    }

    /**
     * Find the given view in the list of paths.
     * 
     * @param  string  $name
     * @param  array  $paths
     * 
     * @return string
     * 
     * @throws \Syscodes\Components\View\Exceptions\ViewException
     */
    protected function findPaths($name, $paths): string
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getFindViewFiles($name) as $file) {
                if ($this->files->exists($view = $path.DIRECTORY_SEPARATOR.$file)) {
                    return $view;
                }
            }
        }
        
       throw new ViewException(__('view.notFound', ['file' => $name]));
    }

    /**
     * Get an array of possible view files.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    protected function getFindViewFiles($name): array
    {
        return array_map(fn ($extension) => str_replace('.', DIRECTORY_SEPARATOR, $name).'.'.$extension, $this->getExtensions());
    }

    /**
     * Resolve the path.
     * 
     * @param  string  $path
     * 
     * @return string
     */
    protected function resolvePath($path): string
    {
        return realpath($path) ?: $path;
    }
    
    /**
     * Add a namespace hint to the finder.
     * 
     * @param  string  $namespace
     * @param  string|array  $hints
     * 
     * @return void
     */
    public function addNamespace($namespace, $hints): void
    {
        $hints = (array) $hints;
        
        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }
        
        $this->hints[$namespace] = $hints;
    }

    /**
     * Replace the namespace hints for the given namespace.
     * 
     * @param  string  $namespace
     * @param  string|array  $hints
     * 
     * @return void
     */
    public function replaceNamespace($namespace, $hints): void
    {
        $this->hints[$namespace] = (array) $hints;
    }

    /**
     * Returns whether or not the view name has any hint information.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    protected function hasHintInfo($name): bool
    {
        return strpos($name, static::HINT_PATH_DELIMITER) > 0;
    }
}