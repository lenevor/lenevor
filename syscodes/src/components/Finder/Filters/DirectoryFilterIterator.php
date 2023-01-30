<?php

namespace Syscodes\Components\Finder\Filters;

use RecursiveFilterIterator;
use Syscodes\Components\Finder\SplFileInfo;

class DirectoryFilterIterator extends RecursiveFilterIterator
{
    /**
     * Constructor. Create a new DirectoryFilterIterator class instance.
     * 
     * @param  string|\RecursiveIterator  $path
     * @param  int  $flags
     * @param  bool  $ignoreDirs
     */
    public function __construct(string $path, int $flags, bool $ignoreDirs = false)
    {
        parent::__construct($path, $flags);
    }

    /**
     * Filters the iterator values.
     * 
     * @return bool
     */
    public function accept(): bool 
    {
        return true;
    }

    /**
     * Return an instance of SplFileInfo with support for relative paths.
     */
    public function current(): SplFileInfo
    {
        return new SplFileInfo('', '', '');
    }
}