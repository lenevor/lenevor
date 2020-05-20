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

namespace Syscodes\View;

use Syscodes\Support\Finder;
use Syscodes\Filesystem\Filesystem;
use Syscodes\Contracts\View\ViewFinder;
use Syscodes\View\Exceptions\ViewException;

/**
 * Allows location of a view.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class FileViewFinder implements ViewFinder
{
    use Extensions;
    
    /**
     * The filesystem instance.
     * 
     * @var string $files
     */
    protected $files;

    /**
     * The array of views that have been located.
     * 
     * @var array $views
     */
    protected $views = [];

    /**
     * Constructor. Create a new FileViewFinder class instance.
     * 
     * @param  \Syscodes\Filesystem\Filesystem  $files
     * @param  array|null  $extensions  (null by default) 
     * 
     * @return void
     */
    public function __construct(Filesystem $files, $extensions = null)
    {
        $this->files = $files;

        if (isset($extensions))
        {
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
    public function find($name)
    {
        if (isset($this->views[$name]))
        {
            return $this->views[$name];
        }

        return $this->views[$name] = $this->findPaths($name);
    }

    /**
     * Find the given view in the list of paths.
     * 
     * @param  string  $name
     * 
     * @return string
     * 
     * @throws \Syscodes\View\Exceptions\ViewException
     */
    protected function findPaths($name)
    {
        foreach ($this->getFindViewFiles($name) as $view)
        {
            if ($this->files->exists($view))
            {
                return $view;
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
    protected function getFindViewFiles($name)
    {
        return array_map(function ($extension) use ($name)
        {
            return Finder::search($name, 'views', $extension);   
        }, $this->getExtensions());
    }
}