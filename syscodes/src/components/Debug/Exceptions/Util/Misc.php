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

namespace Syscodes\Components\Debug\Util;

/**
 * Determines the error level in an exception.
 */
class Misc
{
    /**
     * The errors of php.
     * 
     * @var array $phpErrors
     */
    protected static $phpErrors = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_USER_ERROR,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING
    ];
    
    /**
     * Determine if the error level is fatal.
     * 
     * @param  int  $level
     * 
     * @return bool
     */
    public static function isFatalError(int $level): bool
    {
        return in_array($level, static::$phpErrors);
    }
    
    /**
     * Can we at this point in time send HTTP headers?
     * Currently this checks if we are even serving an HTTP request,
     * as opposed to running from a command line.
     * 
     * If we are serving an HTTP request, we check if it's not too late.
     * 
     * @return bool
     */
    public static function sendHeaders(): bool
    {
        return isset($_SERVER["REQUEST_URI"]) && ! headers_sent();
    }

    /**
     * Translate ErrorException code into the represented constant.
     * 
     * @param  int  $errorCode
     * 
     * @return string
     */
    public static function translateErrorCode($errorCode): string
    {
        $constants = get_defined_constants(true);

        if (array_key_exists('Core', $constants)) {
            foreach ($constants['Core'] as $constant => $value) {
                if (substr($constant, 0, 2) == 'E_' && $value == $errorCode) {
                    return $constant;
                }
            }
        }

        return 'E_UNKNOWN';
    }
}