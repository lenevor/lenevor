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

namespace Syscodes\Components\Console\Helper;

/**
 * Helper is the base class for all helper classes.
 */
class Helper
{
    /**
     * The width is how many characters positions the string will use.
     * 
     * @param  string|null  $value
     * 
     * @return int
     */
    public static function width(?string $value): int
    {
        $value ?? '';
        
        if (false === $encoding = mb_detect_encoding($value, null, true)) {
            return strlen($value);
        }
        
        return mb_strwidth($value, $encoding);
    }
    
    /**
     * The length is related to how many bytes the string will use.
     * 
     * @param  string|null  $value
     * 
     * @return int
     */
    public static function length(?string $value): int
    {
        $value ?? '';
        
        if (false === $encoding = mb_detect_encoding($value, null, true)) {
            return strlen($value);
        }
        
        return mb_strlen($value, $encoding);
    }

    /**
     * Returns the subset of a string, using mb_substr if it is available.
     * 
     * @param  string|null  $value
     * @param  int  $from
     * @param  int|null  $length
     * 
     * @return string
     */
    public static function substr(?string $value, int $from, int $length = null): string
    {
        $value ?? '';
        
        if (false === $encoding = mb_detect_encoding($value, null, true)) {
            return substr($value, $from, $length);
        }
        
        return mb_substr($value, $from, $length, $encoding);
    }
    
    /**
     * Get the format for time of an item result to response time in console.
     * 
     * @param  int|float  $seconds
     * 
     * @return string
     */
    public static function formatTime(int|float $seconds)
    {
        static $timeFormats = [
            [0, '< 1 sec'],
            [1, '1 sec'],
            [2, 'secs', 1],
            [60, '1 min'],
            [120, 'mins', 60],
            [3600, '1 hr'],
            [7200, 'hrs', 3600],
            [86400, '1 day'],
            [172800, 'days', 86400],
        ];
        
        foreach ($timeFormats as $index => $format) {
            if ($seconds >= $format[0]) {
                if ((isset($timeFormats[$index + 1]) &&
                           $seconds < $timeFormats[$index + 1][0]) ||
                           $index == count($timeFormats) - 1
                ) {
                    if (2 == count($format)) {
                        return $format[1];
                    }
                    
                    return floor($seconds / $format[2]).' '.$format[1];
                }
            }
        }
    }
}