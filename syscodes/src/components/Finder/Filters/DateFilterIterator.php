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
 * Gets the date filter for iterator.
 */
class DateFilterIterator extends FilterIterator
{
    /**
     * Get the comparator of operators.
     * 
     * @var array $comparators
     */
    private array $comparators = [];

    /**
     * Constructor. Create a new DateFilteriterator class instance.
     * 
     * @param \Iterator<string, \SplFileInfo>  $iterator  The Iterator to filter
     * @param \Syscodes\Components\Finder\Comparators\DateComparator[]  $comparators
     * 
     * @return void
     */
    public function __construct(Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;

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
        
        if ( ! file_exists($fileinfo->getPathname())) {
            return false;
        }
        
        $filedate = $fileinfo->getMTime();
        
        foreach ($this->comparators as $compare) {
            if ( ! $compare->test($filedate)) {
                return false;
            }
        }
        
        return true;
    }
}