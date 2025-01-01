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

use Syscodes\Components\Contracts\Console\Output\OutputFormatterStyle as OutputFormatterStyleInterface;

/**
 * <OutputFormatter> is the interface for output console.
 */
interface OutputFormatter
{
    /**
     * The pattern to phrase the format.
     */
    public const FORMAT_PATTERN = '#<([a-z][a-z0-9_=;-]+)>(.*?)</\\1?>#is';

    /**
     * Gets style options from style with specified name.
     * 
     * @param  string  $name
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    public function getStyle(string $name): string;

    /**
     * Sets a new style.
     * 
     * @param  string  $name
     * @param  \Syscodes\Components\Contracts\Console\OutputFormatterStyle  $style
     * 
     * @return void
     */
    public function setStyle($name, OutputFormatterStyleInterface $style): void;

    /**
     * Checks if output formatter has style with specified name.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function hasStyle(string $name): bool;

    /**
     * Gets the decorated for styles in messages.
     * 
     * @return bool
     */
    public function getDecorated(): bool;

    /**
     * Sets the decorated for styles in messages.
     * 
     * @param  bool  $decorated
     * 
     * @return void
     */
    public function setDecorated(bool $decorated): void;

    /**
     * Formats a message depending to the given styles.
     * 
     * @param  string  $message
     * 
     * @return string
     */
    public function format(?string $message): string;
}