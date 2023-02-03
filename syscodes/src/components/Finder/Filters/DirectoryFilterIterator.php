<?php

namespace Syscodes\Components\Finder\Filters;

use RecursiveDirectoryIterator;
use Syscodes\Components\Finder\SplFileInfo;

class DirectoryFilterIterator extends RecursiveDirectoryIterator
{
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
    }

    /**
     * Return an instance of SplFileInfo with support for relative paths.
     */
    public function current(): SplFileInfo
    {
        return new SplFileInfo('', '', '');
    }
}