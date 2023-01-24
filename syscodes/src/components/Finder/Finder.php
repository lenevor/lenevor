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

namespace Syscodes\Components\Finder;

use Countable;
use Traversable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Gets the results of search in files and directories.
 */
class Finder implements IteratorAggregate, Countable
{
    /**
     * Constructor. Create a new Finder class instance.
     * 
     * @return void
     */
    public function __construct()
    {
        
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
        return new ArrayIterator($this);
    }
}