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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Formatter;

use Syscodes\Components\Contracts\Console\Output\OutputFormatterStyle as OutputFormatterStyleInterface;

/**
 * Allows that formatter style class.
 */
class OutputFormatterStack
{
    /**
     * The empty styles.
     * 
     * @var \Syscodes\Components\Contracts\Console\OutputFormatterStyle $emptyStack
     */
    protected $emptyStack;

    /**
     * Gets the styles for the text commands.
     * 
     * @var array|\Syscodes\Components\Contracts\Console\OutputFormatterStyle $styles
     */
    protected $styles;

    /**
     * Constructor. Create a new OutputFormatterStack instance.
     * 
     * @param  \Syscodes\Components\Contracts\Console\OutputFormatterStyle|null  $emptyStack
     * 
     * @return void
     */
    public function __construct(OutputFormatterStyleInterface $emptyStack = null)
    {
        $this->emptyStack = $emptyStack ?? new OutputFormatterStyle();

        $this->reset();
    }

    /**
     * Resets stack.
     * 
     * @return mixed
     */
    public function reset()
    {
        $this->styles = [];
    }

    /**
     * Pushes a style in the stack.
     * 
     * @param  \Syscodes\Components\Contracts\Console\OutputFormatterStyle|null  $style
     * 
     * @return \Syscodes\Components\Console\Formatter\OutputFormatterStyle
     */
    public function push(OutputFormatterStyleInterface $style =  null)
    {
        $this->styles[] = $style;
    }

    /**
     * Gets current style with stacks top codes.
     * 
     * @return \Syscodes\Components\Console\Formatter\OutputFormatterStyle
     */
    public function getCurrent()
    {
        if (empty($this->styles)) {
            return $this->emptyStack;
        }

        $this->styles[count($this->styles) - 1];
    }
}