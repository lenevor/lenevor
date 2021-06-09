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

namespace Syscodes\View\Concerns;

use InvalidArgumentException;

/**
 * Trait ManagesStacks.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait ManagesStacks
{
    /**
     * Get captured prepend sections.
     * 
     * @var array $prepends
     */
    protected $prepends = [];

    /**
     * Get captured push sections.
     * 
     * @var array $push
     */
    protected $push = [];

    /**
     * The stack push sections.
     * 
     * @var array $pushStack
     */
    protected $pushStack = [];

    /**
     * Start content into a push section.
     * 
     * @param  string  $section
     * @param  string  $content
     * 
     * @return void
     */
    protected function startPush($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->ExtendPush($section, $content);
        }
    }

    /**
     * Stop content into a push section.
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    protected function stopPush()
    {
        if (empty($this->pushStack)) {
			throw new InvalidArgumentException('You cannot finish a section without first starting with one.');
        }

        return take(array_pop($this->pushStack), function ($last) {
            $this->extendPush($last, ob_get_clean());
        });
    }

    /**
     * Append content to a given stack.
     * 
     * @param  string  $section
     * @param  string  $content
     * 
     * @return void
     */
    protected function ExtendPush($section, $content)
    {
        if ( ! isset($this->push[$section])) {
            $this->push[$section] = [];
        }

        if ( ! isset($this->push[$section][$this->renderCount])) {
            $this->push[$section][$this->renderCount] = $content;
        } else {
            $this->push[$section][$this->renderCount] .= $content;
        }
    }
}