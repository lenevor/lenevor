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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the Plaze engine class facade.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static void transpile($path = null)
 * @method static string displayString(string $value)
 * @method static string stripParentheses(string $expression)
 * @method static void extend(callable $extend)
 * @method static array getExtensions()
 * 
 * @see \Syscodes\Components\View\Transpilers\PlazeTranspiler
 */
class Plaze extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'plaze.transpiler';
    }
}