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
 * Initialize the Http class facade.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static bool isCli()
 * @method static bool isSecure()
 * @method static string protocol()
 * @method static string|array cookie(string $index = null, mixed $default = null)
 * @method static string|array file(string $index = null, mixed $default = null)
 * @method static string|array server(string $index = null, mixed $default = null)
 * @method static string detectPath(string $protocol = '')
 * @method static string parseBaseUrl()
 * @method static string parsePathInfo()
 * 
 * @see \Syscodes\Components\Http\Http
 */
class Http extends Facade
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
        return 'http';
    }
}