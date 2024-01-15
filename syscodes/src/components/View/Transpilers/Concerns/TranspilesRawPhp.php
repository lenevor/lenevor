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

namespace Syscodes\Components\View\Transpilers\Concerns;

/**
 * Trait TranspilesRawPhp.
 */
trait TranspilesRawPhp
{
    /**
     * Transpile the raw PHP statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpilePhp($expression): string
    {
        if ($expression) {
            return "<?php {$expression}; ?>";
        }

        return '<@php';
    }

    /**
     * Transpile the unset statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileUnset($expression): string
    {
        return "<?php unset{$expression}; ?>";
    }
}