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

namespace Syscodes\Contracts\Console;

/**
 * <OutputFormatter> is the interface for output console.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface OutputFormatter
{
    /**
     * Gets style options from style with specified name.
     * 
     * @return \Syscodes\Contracts\Console\OutputFormatterStyles
     */
    public function getStyle(): OutputFormatterStyles;

    /**
     * Sets a new style.
     * 
     * @param  string  $name
     * @param  \Syscodes\Contracts\Console\OutputFormatterStyles  $style
     * 
     * @return void
     */
    public function setStyle(string $name, OutputFormatterStyles $style): void;

    /**
     * Checks if output formatter has style with specified name.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function hasStyle(string $name): bool;

    /**
     * Formats a message depending to the given styles.
     * 
     * @param  string  $message
     * 
     * @return string
     */
    public function format(?string $message): string;
}