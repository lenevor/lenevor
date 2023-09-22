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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Finder\Concerns;

use Iterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Syscodes\Components\Finder\Finder;
use Syscodes\Components\Finder\Filters\DateFilterIterator;
use Syscodes\Components\Finder\Filters\FileFilterIterator;
use Syscodes\Components\Finder\Filters\DirectoryFilterIterator;

/**
 * Allows helper trait methods in Finder class.
 */
trait FinderHelper
{
    /**
     * Search in directories for iterator.
     * 
     * @param  string  $dir
     * 
     * @return \Iterator
     */
    private function searchInDirectory(string $dir): Iterator
    {
        $notPaths = [];
        
        if (Finder::IGNORE_DOT_FILES === (Finder::IGNORE_DOT_FILES & $this->ignore)) {
            $notPaths[] = '#(^|/)\..+(/|$)#';
        }

        $flags    = RecursiveDirectoryIterator::SKIP_DOTS;
        $iterator = new DirectoryFilterIterator($dir, $flags, $this->ignoreDirs);
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

        if ($this->mode) {
            $iterator = new FileFilterIterator($iterator, $this->mode);
        }

        if ($this->dates) {
            $iterator = new DateFilterIterator($iterator, $this->dates);
        }

        return $iterator;
    }

    /**
     * Normalizes given directory names by removing trailing slashes.
     * 
     * @param  string  $dir
     * 
     * @return string
     */
    private function normalizeDir(string $dir): string
    {
        if ('/' === $dir) {
            return $dir;
        }
        
        $dir = rtrim($dir, '/'.DIRECTORY_SEPARATOR);
        
        if (preg_match('~^(ssh2\.)?s?ftp://~', $dir)) {
            $dir .= '/';
        }
        
        return $dir;
    }
}