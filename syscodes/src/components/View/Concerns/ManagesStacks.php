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

namespace Syscodes\Components\View\Concerns;

use InvalidArgumentException;

/**
 * Trait ManagesStacks.
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
     * Start injecting content into a push section.
     * 
     * @param  string  $section
     * @param  string  $content
     * 
     * @return void
     */
    public function startPush($section, $content = ''): void
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
     * Stop injecting content into a push section.
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function stopPush()
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
    protected function ExtendPush($section, $content): void
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

    /**
     * Start prepending content into a push section.
     * 
     * @param  string  $section
     * @param  string  $content
     * 
     * @return void
     */
    public function startPrepend($section, $content = ''): void
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->ExtendPrepend($section, $content);
        }
    }

    /**
     * Stop prepending content into a push section.
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function stopPrepend()
    {
        if (empty($this->pushStack)) {
			throw new InvalidArgumentException('You cannot finish a section without first starting with one.');
        }

        return take(array_pop($this->pushStack), function ($last) {
            $this->extendPrepend($last, ob_get_clean());
        });
    }

    /**
     * Prepend content to a given stack.
     * 
     * @param  string  $section
     * @param  string  $content
     * 
     * @return void
     */
    protected function ExtendPrepend($section, $content): void
    {
        if ( ! isset($this->prepends[$section])) {
            $this->prepends[$section] = [];
        }

        if ( ! isset($this->prepends[$section][$this->renderCount])) {
            $this->prepends[$section][$this->renderCount] = $content;
        } else {
            $this->prepends[$section][$this->renderCount] = $content.$this->prepends[$section][$this->renderCount];
        }
    }

    /**
     * Get the string contents of a push section.
     * 
     * @param  string  $section
     * @param  string  $default
     * 
     * @return string
     */
    public function givePushContent($section, $default = ''): string
    {
        if ( ! isset($this->push[$section]) && ! isset($this->prepends[$section])) {
            return $default;
        }

        $result = '';

        if (isset($this->prepends[$section])) {
            $result .= implode(array_reverse($this->prepends[$section]));
        }

        if (isset($this->push[$section])) {
            $result .= implode($this->push[$section]);
        } 

        return $result;
    }

    /**
     * Flush all of the stacks.
     * 
     * @return void
     */
    public function flushStacks(): void
    {
        $this->prepends  = [];
        $this->push      = [];
        $this->pushStack = [];
    }
}