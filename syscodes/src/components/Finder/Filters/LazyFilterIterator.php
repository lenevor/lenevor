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

use Closure;
use IteratorAggregate;
use Traversable;

/**
 * Gets the lazy filter for iterator.
 */
class LazyFilterIterator implements IteratorAggregate
{
    /**
     * Get the iterator.
     * 
     * @var Closure $iterator
     */
    private Closure $iterator;

    /**
     * Constructor. Create a new LazyFilterIterator instance.
     * 
     * @param  callable  $iterator
     * 
     * @return void
     */
    public function __construct(callable $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * Retrieve an external iterator for the current.
     * 
     * @return \Iterator
     */
    public function getIterator(): Traversable
    {
        yield from ($this->iterator)();
    }
}