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

namespace Syscodes\Components\Support;

/**
 * Allows escapes a string.
 */
class PromptUtility
{
    /**
     * Escapes a string to be used as a shell argument.
     *
     * @param  string  $argument
     * 
     * @return string
     */
    public static function escapeArgument($argument): string
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            if ($argument === '') {
                return '""';
            }

            $escapedArgs = '';
            $quote = false;

            foreach (preg_split('~(")~', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
                if ($part === '"') {
                    $escapedArgs .= '\\"';
                } elseif (self::isSurroundedBy($part, '%')) {
                    // Avoid environment variable expansion
                    $escapedArgs .= '^%"'.substr($part, 1, -1).'"^%';
                } else {
                    // escape trailing backslash
                    if (str::endsWith($part, '\\')) {
                        $part .= '\\';
                    }
                    $quote = true;
                    $escapedArgs .= $part;
                }
            }

            if ($quote) {
                $escapedArgs = '"'.$escapedArgs.'"';
            }

            return $escapedArgs;
        }

        return "'".str_replace("'", "'\\''", $argument)."'";
    }

    /**
     * Allows the given string to be surrounded by a given character.
     *
     * @param  string  $args
     * @param  string  $char
     * 
     * @return bool
     */
    protected static function isSurroundedBy(string $args, string $char): bool
    {
        return strlen($args) > 2 && $char === $args[0] && $char === $args[strlen($args) - 1];
    }
}