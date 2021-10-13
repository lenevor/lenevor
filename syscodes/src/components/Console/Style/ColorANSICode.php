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

namespace Syscodes\Components\Console\Style;

/**
 * The ANSI code for use the colors and options format on CLI command. 
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class ColorANSICode
{
    public const RESET  = 0;
    public const NORMAL = 0;
    
    // Foreground color
    public const FG_BLACK   = 30;
    public const FG_RED     = 31;
    public const FG_GREEN   = 32;
    public const FG_BROWN   = 33; // like yellow
    public const FG_BLUE    = 34;
    public const FG_CYAN    = 36;
    public const FG_WHITE   = 37;
    public const FG_DEFAULT = 39;
    
    // Extra Foreground color
    public const FG_DARK_GRAY     = 90;
    public const FG_LIGHT_RED     = 91;
    public const FG_LIGHT_GREEN   = 92;
    public const FG_LIGHT_YELLOW  = 93;
    public const FG_LIGHT_BLUE    = 94;
    public const FG_LIGHT_MAGENTA = 95;
    public const FG_LIGHT_CYAN    = 96;
    public const FG_LIGHT_WHITE   = 97;
    
    // Background color
    public const BG_BLACK   = 40;
    public const BG_RED     = 41;
    public const BG_GREEN   = 42;
    public const BG_BROWN   = 43; // like yellow
    public const BG_BLUE    = 44;
    public const BG_CYAN    = 46;
    public const BG_WHITE   = 47;
    public const BG_DEFAULT = 49;
    
    // Extra Background color
    public const BG_DARK_GRAY     = 100;
    public const BG_LIGHT_RED     = 101;
    public const BG_LIGHT_GREEN   = 102;
    public const BG_LIGHT_YELLOW  = 103;
    public const BG_LIGHT_BLUE    = 104;
    public const BG_LIGHT_MAGENTA = 105;
    public const BG_LIGHT_CYAN    = 106;
    public const BG_LIGHT_WHITE   = 107;
    
    // Color options
    public const BOLD      = 1;
    public const FUZZY     = 2;
    public const ITALIC    = 3;
    public const UNDERLINE = 4;
    public const BLINK     = 5;
    public const REVERSE   = 7;
    public const CONCEALED = 8;
}