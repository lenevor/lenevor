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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Console\Output;

/**
 * <OutputFormatterStyle> is the interface for defining styles.
 */
interface OutputFormatterStyle
{
    /**
     * Sets style foreground color.
     * 
     * @param  string|null  $color
     * 
     * @return void When the color name isn't defined
     */
    public function setForeground(?string $color = null): void;

    /**
     * Sets style background color.
     * 
     * @param  string|null  $color
     * 
     * @return void When the color name isn't defined
     */
    public function setBackground(?string $color = null): void;

    /**
     * Sets some specific style option.
     * 
     * @param  string  $option
     * 
     * @return void
     */
    public function setOption(string $option): void;

    /**
     * Unsets some specific style option.
     * 
     * @param  string  $option
     * 
     * @return void
     */
    public function unsetOption(string $option): void;

    /**
     * Sets multiple style options at once.
     * 
     * @param  array  $option
     * 
     * @return void
     */
    public function setOptions(array $options): void;

    /**
     * Applies the style to a given text.
     * 
     * @param  string  $text
     * 
     * @return string
     */
    public function apply(string $text): string;
}