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

use Iterator;
use FilterIterator;

/**
 * Gets the file filter for iterator.
 */
class FileFilterIterator extends FilterIterator
{
    public const ONLY_FILES = 1;
    public const ONLY_DIRECTORIES = 2;
    
    /**
     * Get the mode for a given file.
     * 
     * @var int $mode
     */
    private int $mode;
    
    /**
     * Constructor. Create a new FileFilterIterator class instance.
     * 
     * @param \Iterator<string, \SplFileInfo>  $iterator  The Iterator to filter
     * @param int  $mode  The mode (self::ONLY_FILES or self::ONLY_DIRECTORIES)
     * 
     * @return void
     */
    public function __construct(Iterator $iterator, int $mode)
    {
        $this->mode = $mode;
        
        parent::__construct($iterator);
    }
    
    /**
     * Filters the iterator values.
     * 
     * @return bool
     */
    public function accept(): bool
    {
        $fileinfo = $this->current();
        
        if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES & $this->mode) && $fileinfo->isFile()) {
            return false;
        } elseif (self::ONLY_FILES === (self::ONLY_FILES & $this->mode) && $fileinfo->isDir()) {
            return false;
        }
        
        return true;
    }
}