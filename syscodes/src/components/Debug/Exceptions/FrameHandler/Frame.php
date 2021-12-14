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

use Serializable;

/**
 * Returns the content of an exception through a trace.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Frame implements Serializable
{
    /**
     * @var array $comments
     */
    protected $comments = [];

    /**
     * @var array $frame
     */
    protected $frame;

    /**
     * Constructor. Initialize Frame class
     * 
     * @param  array  $frame
     * 
     * @return void
     */
    public function __construct(array $frame)
    {
        $this->frame = $frame;
    }

    /**
     * Gets the trace class of a file.
     * 
     * @return string|null
     */
    public function getClass()
    {
        return isset($this->frame['class']) ? $this->frame['class'] : null;
    }

    /**
     * Gets the trace path of a file.
     * 
     * @return string|null
     */
    public function getFile()
    {
        if (empty($this->frame['file'])) {
            return null;
        }

        return $this->frame['file'];
    }

    /**
     * Returns the array containing the raw frame data from which
     * this Frame object was built.
     * 
     * @return array
     */
    public function getFrame(): array
    {
        return $this->frame;
    }

    /**
     * Gets the trace function of a file.
     * 
     * @return string|null
     */
    public function getFunction()
    {
        return isset($this->frame['function']) ? $this->frame['function'] : null;
    }

    /**
     * Gets the trace line of a file.
     * 
     * @return int|null
     */
    public function getLine()
    {
        return isset($this->frame['line']) ? $this->frame['line'] : null;
    }

    /**
     * Gets the trace args of a file.
     * 
     * @return array
     */
    public function getArgs(): array
    {
        return isset($this->frame['args']) ? (array) $this->frame['args'] : [];
    }

    /**
     * Adds a comment to this frame can be used by other handlers.
     * By default, it is used by the PleasingPageHandler handler.
     * An interesting use for comments would be, for example, code 
     * analysis, annotations, etc.
     * 
     * @param  string  $comment
     * @param  string  $context  Optional
     * 
     * @return void
     */
    public function addComment($comment, $context = 'default'): void
    {
        $this->comments[] = [
            'comments' => $comment,
            'context'  => $context
        ];
    }

    /**
     * Returns all comments for this frame. Optionally allows
     * a filter to only retrieve comments from a specific context.
     * 
     * @param  string|null  $filter
     * 
     * @return array
     */
    public function getComments($filter = null): array
    {
        $comments = $this->comments;

        if ($filter !== null) {
            $comments = array_filter($comments, function ($comment) use ($filter) {
                return $comment['context'] == $filter;
            });
        }

        return $comments;
    }

    /*
    |-----------------------------------------------------------------
    | Serializable Methods
    |-----------------------------------------------------------------
    */

    /**
     * Implements the Serializable interface.
     * 
     * @see    Serializable::serialize
     * 
     * @return string
     */
    public function serialize()
    {
        $frame = $this->frame;

        return serialize($frame);
    }

    /**
     * Unserializes the frame data.
     * 
     * @see    Serializable::unserialize 
     * @param  string  $frame
     *  
     * @return void
     */
    public function unserialize($frame)
    {
        $frame = unserialize($frame);

        $this->frame = $frame;
    }
}