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

namespace Syscodes\Components\Finder;

use AppendIterator;
use Countable;
use Traversable;
use LogicException;
use IteratorIterator;
use IteratorAggregate;
use Syscodes\Components\Finder\Concerns\FinderHelper;
use Syscodes\Components\Finder\Comparators\DateComparator;
use Syscodes\Components\Finder\Filters\FileFilterIterator;
use Syscodes\Components\Finder\Exceptions\DirectoryNotFoundException;
use Syscodes\Components\Finder\Filters\LazyFilterIterator;

/**
 * Gets the results of search in files and directories.
 */
class Finder implements IteratorAggregate, Countable
{
    use FinderHelper;

    public const IGNORE_VCS_FILES = 1;
    public const IGNORE_DOT_FILES = 2;

    /**
     * Get the file date.
     * 
     * @var array $dates
     */
    private array $dates = [];

    /**
     * Get the directories.
     * 
     * @var array $dirs
     */
    private array $dirs = [];

    /**
     * Get ignore for given type file. 
     * 
     * @var int $ignore
     */
    private int $ignore = 0;

    /**
     * Get ignore dirs for given type file.
     * 
     * @var bool $ignoreDirs
     */
    private bool $ignoreDirs = false;

    /**
     * Get the mode for file.
     * 
     * @var int $mode
     */
    private int $mode = 0;

    /**
     * Get the names to type of the files.
     * 
     * @var array $names
     */
    private array $names = [];

    /**
     * Get the names not type of the files.
     * 
     * @var array $names
     */
    private array $notNames = [];

    /**
     * Get the path not of files.
     * 
     * @var array $notPaths
     */
    private array $notPaths = [];

    /**
     * Get the path of file.
     * 
     * @var array $paths
     */
    private array $paths = [];

    /**
     * Constructor. Create a new Finder class instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }
    
    /* Creates a new Finder instance.
     * 
     * @return static
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * Restricts the matching to files only.
     * 
     * @return static
     */
    public function files(): static
    {
        $this->mode = FileFilterIterator::ONLY_FILES;

        return $this;
    }

    /**
     * Adds filters for file dates (last modified).
     * 
     * @param  string|string[]  $dates
     * 
     * @return static
     */
    public function date(string|array $dates): static
    {
        foreach ((array) $dates as $date) {
            $this->dates[] = new DateComparator($date);
        }

        return $this;
    }
    
    /**
     * Adds rules that files must match.
     * 
     * @param  string|string[]  $patterns  A pattern (a regexp, a glob, or a string) or an array of patterns
     * 
     * @return static
     */
    public function name(string|array $patterns): static
    {
        $this->names = array_merge($this->names, (array) $patterns);
        
        return $this;
    }
    
    /**
     * Adds rules that files must not match.
     * 
     * @param  string|string[]  $patterns  A pattern (a regexp, a glob, or a string) or an array of patterns
     * 
     * @return static
     */
    public function notName(string|array $patterns): static
    {
        $this->notNames = array_merge($this->notNames, (array) $patterns);
        
        return $this;
    }
    
    /**
     * Adds rules that filenames must match.
     * 
     * @param  string|string[]  $patterns
     * 
     * @return static
     */
    public function path(string|array $patterns): static
    {
        $this->paths = array_merge($this->paths, (array) $patterns);
        
        return $this;
    }
    
    /**
     * Adds rules that filenames must not match.
     * 
     * @param  string|string[]
     * 
     * @return static
     */
    public function notPath(string|array $patterns): static
    {
        $this->notPaths = array_merge($this->notPaths, (array) $patterns);
        
        return $this;
    }

    /**
     * Excludes "hidden" directories and files (starting with a dot).
     * 
     * @param  bool  $ignore
     * 
     * @return static
     */
    public function ignoreDotFiles(bool $ignore): static
    {
        if ($ignore) {
            $this->ignore |= static::IGNORE_DOT_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_FILES;
        }

        return $this;
    }
    
    /**
     * Searches files and directories which match defined rules.
     * 
     * @param  string|string[]  $dirs  A directory path or an array of directories
     * 
     * @return static
     * 
     * @throws DirectoryNotFoundException  if one of the directories does not exist
     */
    public function in(string|array $dirs): static
    {
        $resolvedDirs = [];
        
        foreach ((array) $dirs as $dir) {
            if (is_dir($dir)) {
                $resolvedDirs[] = [$this->normalizeDir($dir)];
            } elseif ($glob = glob($dir, (defined('GLOB_BRACE') ? GLOB_BRACE : 0) | GLOB_ONLYDIR | GLOB_NOSORT)) {
                sort($glob);
                
                $resolvedDirs[] = array_map($this->normalizeDir('...'), $glob);
            } else {
                throw new DirectoryNotFoundException(sprintf('The "%s" directory does not exist', $dir));
            }
        }
        
        $this->dirs = array_merge($this->dirs, ...$resolvedDirs);
        
        return $this;
    }
    
    /**
     * Counts all the results collected by the iterators.
     * 
     * @return int
     */
    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    /**
     * Retrieve an external iterator for the current Finder configuration.
     * 
     * @return \Iterator
     * 
     * @throws \LogicException
     */
    public function getIterator(): Traversable
    {
        if (0 === count($this->dirs)) {
            throw new LogicException('You must call one of in() or append() methods before iterating over a Finder');
        }

        if (1 === count($this->dirs)) {
            $iterator = $this->searchInDirectory($this->dirs[0]);

            return $iterator;
        }

        $iterator = new AppendIterator();

        foreach ($this->dirs as $dir) {
            $iterator->append(new IteratorIterator(
                new LazyFilterIterator(fn () => $this->searchInDirectory($dir)))
            );
        }

        return $iterator;
    }
}