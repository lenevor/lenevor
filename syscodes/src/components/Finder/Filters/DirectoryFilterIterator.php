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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Finder\Filters;

use RecursiveDirectoryIterator;
use UnexpectedValueException;
use Syscodes\Components\Finder\Exceptions\AccessDeniedException;
use Syscodes\Components\Finder\SplFileInfo;

/**
 * Gets the directory filter for iterator.
 */
class DirectoryFilterIterator extends RecursiveDirectoryIterator
{
    /**
     * Get the directory separator for folders or files.
     * 
     * @var string $ds
     */
    private string $ds = '/';

    /**
     * The ignore directories or files.
     * 
     * @var bool $ignoreDirs
     */
    private bool $ignoreDirs;

    /**
     * Is ignored to rewind dir back to the start.
     * 
     * @var bool $ignoreRewind
     */
    private bool $ignoreRewind = true;

    /**
     * Get the root path.
     * 
     * @var string $rootPath
     */
    private string $rootPath;

    /**
     * Get the sub path.
     * 
     * @var string $subPath
     */
    private string $subPath;

    /**
     * Constructor. Create a new DirectoryFilterIterator class instance.
     * 
     * @param  string|mixed  $path
     * @param  int  $flags
     * @param  bool  $ignoreDirs
     */
    public function __construct(string $path, int $flags, bool $ignoreDirs = false)
    {
        parent::__construct($path, $flags);
        
        $this->ignoreDirs = $ignoreDirs;
        $this->rootPath   = $path;
        
        if ('/' !== DIRECTORY_SEPARATOR && ! ($flags & self::UNIX_PATHS)) {
            $this->ds = DIRECTORY_SEPARATOR;
        }
    }
    
    /**
     * Return an instance of SplFileInfo with support for relative paths.
     * 
     * @return \Syscodes\Components\Finder\SplFileInfo
     */
    public function current(): SplFileInfo
    {
        if ( ! isset($this->subPath)) {
            $this->subPath = $this->getSubPath();
        }
        
        $subPathname = $this->subPath;
        
        if ('' !== $subPathname) {
            $subPathname .= $this->ds;
        }
        
        $subPathname .= $this->getFilename();
        
        if ('/' !== $basePath = $this->rootPath) {
            $basePath .= $this->ds;
        }
        
        return new SplFileInfo($basePath.$subPathname, $this->subPath, $subPathname);
    }
    
    /**
     * Returns whether current entry is a directory and not '.' or '..'.
     * 
     * @param  bool  $value
     * 
     * @return bool
     */
    public function hasChildren(bool $value = false): bool
    {
        $hasChildren = parent::hasChildren($value);
        
        if ( ! $hasChildren || ! $this->ignoreDirs) {
            return $hasChildren;
        }
        
        try {
            parent::getChildren();
            
            return true;
        } catch (UnexpectedValueException) {
            return false;
        }
    }
    
    /**
     * Returns an iterator for the current entry if it is a directory.
     * 
     * @return \RecursiveDirectoryIterator
     * 
     * @throws \Syscodes\Components\Finder\Exceptions\AccessDeniedException
     */
    public function getChildren(): RecursiveDirectoryIterator
    {
        try {
            $children = parent::getChildren();
            
            if ($children instanceof self) {
                $children->ignoreDirs = $this->ignoreDirs;

                $children->rootPath = $this->rootPath;
            }
            
            return $children;
        } catch (UnexpectedValueException $e) {
            throw new AccessDeniedException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Move to next entry of directory.
     * 
     * @return void
     */
    public function next(): void
    {
        $this->ignoreRewind = false;
        
        parent::next();
    }
    
    /**
     * Rewind dir back to the start.
     * 
     * @return void
     */
    public function rewind(): void
    {
        if ($this->ignoreRewind) {
            $this->ignoreRewind = false;
            
            return;
        }
        
        parent::rewind();
    }
}