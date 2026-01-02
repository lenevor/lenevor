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

namespace Syscodes\Components\Console\View\Components;

use Syscodes\Components\Contracts\Console\NewLineInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renders the given component.
 */
class Line extends Component
{
    /**
     * The possible line styles.
     *
     * @var array
     */
    protected static $styles = [
        'info' => [
            'bgColor' => 'blue',
            'fgColor' => 'white',
            'title' => 'info',
        ],
        'success' => [
            'bgColor' => 'green',
            'fgColor' => 'white',
            'title' => 'success',
        ],
        'warning' => [
            'bgColor' => 'yellow',
            'fgColor' => 'black',
            'title' => 'warning',
        ],
        'error' => [
            'bgColor' => 'red',
            'fgColor' => 'white',
            'title' => 'error',
        ],
    ];

    /**
     * Renders the component using the given arguments.
     *
     * @param  string  $style
     * @param  string  $string
     * @param  int  $verbosity
     * 
     * @return void
     */
    public function render($style, $string, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $string = $this->mutate($string, [
            Mutators\EnsureDynamicContentHighlighted::class,
            Mutators\EnsureRelativePaths::class,
        ]);
        
        $this->renderView('line', array_merge(static::$styles[$style], [
            'marginTop' => $this->output instanceof NewLineInterface ? min(0, 2 - $this->output->newLinesWritten()) : 1,
            'content' => $string,
        ]), $verbosity);
    }
}