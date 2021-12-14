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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Debug\FrameHandler;

use Countable;
use Exception;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Exposes a fluent interface for dealing with an ordered list
 * of stack-trace frames.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array $frames
     */
    protected $frames;

    /**
     * Constructor. Initialize Collection class.
     * 
     * @param  array  $frames
     * 
     * @return array
     */
    public function __construct(array $frames)
    {
        $this->frames = array_map(function ($frame) {
            return new Frame($frame);
        }, $frames);
    }

    /**
     * Returns an array with all frames.
     * 
     * @see    Collection::getIterator
     * 
     * @return array
     */
    public function getArray(): array
    {
        return $this->frames;
    }
    
    /**
     * Array of Frame instances.
     * 
     * @param  array  $frames
     * 
     * @return array
     */
    public function prependFrames(array $frames): array
    {
        $this->frames = array_merge($frames, $this->frames);
    }

    /*
    |-----------------------------------------------------------------
    | ArrayAccess Methods
    |-----------------------------------------------------------------
    */

    /**
     * Whether or not an offset exists.
     * 
     * @see    \ArrayAccess::offsetExists($offset)
     * 
     * @return int
     */
    public function offsetExists($offset)
    {
        return isset($this->frames[$offset]);
    }

    /**
     * Retrieve a value offset.
     * 
     * @see    \ArrayAccess::offsetGet($offset)
     * @param  int  $offset
     * 
     * @return int
     */
    public function offsetGet($offset)
    {
        return $this->frames[$offset];
    }

    /**
     * Assigns a value to the specified offset.
     * 
     * @see    \ArrayAccess::offsetSet($offset, $value)
     * @param  int  $offset
     * 
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception(__CLASS__.' is read only');
    }

    /**
     * Unset an offset.
     * 
     * @see    \ArrayAccess::offsetUnset($offset)
     * @param  int  $offset
     * 
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception(__CLASS__.' is read only');
    }

    /*
    |-----------------------------------------------------------------
    | IteratorAggregate Method
    |-----------------------------------------------------------------
    */

    /**
     * Retrieve an external iterator.
     * 
     * @see    \IteratorAggregate::getIterator
     * 
     * @return new \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->frames);
    }

    /*
    |-----------------------------------------------------------------
    | Countable Method
    |-----------------------------------------------------------------
    */

    /**
     * Count all elements of an object Frame.
     * 
     * @see    Countable::count
     * 
     * @return int
     */
    public function count()
    {
        return count($this->frames);
    }
}