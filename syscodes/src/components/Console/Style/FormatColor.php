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

namespace Syscodes\Components\Console\Style;

/**
 * Use the format of color for tags.
 */
class FormatColor
{
    public const COLORS = [
        'black'   => 0,
        'red'     => 1,
        'green'   => 2,
        'yellow'  => 3,
        'blue'    => 4,
        'magenta' => 5,
        'cyan'    => 6,
        'white'   => 7,
        'default' => 9,
    ];

    public const BRIGHT_COLORS = [
        'gray'           => 0,
        'bright-red'     => 1,
        'bright-green'   => 2,
        'bright-yellow'  => 3,
        'bright-blue'    => 4,
        'bright-magenta' => 5,
        'bright-cyan'    => 6,
        'bright-white'   => 7,
    ];

    public const AVAILABLE_OPTIONS = [
        'bold'       => 1,
        'underscore' => 4,
        'blink'      => 5,
        'reverse'    => 7,
        'conceal'    => 8,
	];
}