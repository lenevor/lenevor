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

namespace Syscodes\Support;

use Syscodes\Collections\Arr;

/**
 * Allows convert a string in diferentes modes of text presentation, either, 
 * camel-cased, studlycaps and replace characters in a string.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Str
{
    /**
     * The cache of camel-cased words.
     *
     * @var array $camelCache
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array $studlyCache
     */
    protected static $studlyCache = [];

    /**
     * Transliterate a UTF-8 value to ASCII.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public static function ascii($value)
    {
        return str_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * Convert the string with spaces or underscore in camelcase notation.
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function camelcase($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        // Notacion lowerCamelCase
        return static::$camelCache[$value] = lcfirst(self::studlycaps($value));
    }

    /**
     * Determine if a given string contains a given substring.
     * 
     * @param  string  $haystack
     * @param  string|array  $needles
     * 
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     *
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace in the chain the spaces by dashes.
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function dash($value)
    {
        return strtr($value, ' ', '-');
    }

    /**
     * Replace in an string the underscore or dashed by spaces.
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function humanize($value)
    {
        return strtr($value, '_-', '  ');
    }

    /**
     * Determine if a given string matches a given pattern.
     * 
     * @param  string  $pattern
     * @param  string  $value
     * 
     * @return bool
     */
    public static function is($pattern, $value)
    {
        $patterns = Arr::wrap($pattern);

        if (is_null($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translate into regular expression wildcards to verify a string
            $pattern = str_replace('\*', '.*', $pattern).'\z';

            if (preg_match('#^'.$pattern.'#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the length of the given string.
     *
     * @param  string  $value  String to length
     * 
     * @return int
     */
    public static function length($value)
    {
        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     *
     * @return tring
     *
     * @uses   \Syscodes\Support\Str::length
     */
    public static function limit($value, $limit, $end = '...')
    {
        if (static::length($value) <= $limit) return $value;

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')).$end;
    }

    /** 
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value);
    }
    
    /**
     * Pad a string with the length of another string. 
     * 
     * @param  string  $string
     * @param  int  $padLength
     * @param  string  $padString
     * @param  string  $padType
     * 
     * @return string 
     */
    public static function pad(
        string $string, 
        int $padLength,
        string $padString = ' ',
        string $padType = 'right'
    ) {
        $type = '';

        switch($padType) {
            case 'right':
                (int) $type = STR_PAD_RIGHT;
                break;
            case 'left':
                (int) $type = STR_PAD_LEFT;
                break;
            case 'both':
                (int) $type = STR_PAD_BOTH;
                break;
        }

        return $padLength > 0 ? \str_pad($string, $padLength, $padString, $type) : $string;
    }

    /**
     * Parse a Class@method style callback into class and method.
     * Puts the class name with the first capital letter.
     * 
     * @param  string       $callback
     * @param  string|null  $default   
     * 
     * @return array
     */
    public static function parseCallback($callback, $default = null)
    {
        return static::contains($callback, '@') ? explode('@', ucfirst($callback), 2) : [$callback, $default];
    }
    
    /**
     * Generate a more truly "random" alpha-numeric string.
     * 
     * @param  int  $length  
     * 
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';
        
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        
        return $string;
    }

    /**
     * Replace a given value in the string sequentially with an array.
     * 
     * @param  string  $search
     * @param  array  $replace
     * @param  string  $subject
     * 
     * @return string
     */
    public static function replaceArray($search, array $replace, $subject)
    {
        $result = '';
        
        foreach ($replace as $value) {
            $result = preg_replace('/'.$search.'/', $value, $subject, 1);
        }

        return $result;
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     * 
     * @param  string  $title
     * @param  string  $separator
     * 
     * @return string
     */
    public static function slug($title, $separator = '-')
    {
        $title = static::ascii($title);
        
        // Convert all dashes/underscores into separator
        $flip  = $separator == '-' ? '_' : '-';
        $title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);
        
        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));
        
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);
        
        return trim($title, $separator);
    }

    /**
     * Converts the CamelCase string into smallcase notation.
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function smallcase($value)
    {
        return mb_strtolower(preg_replace('/([A-Z])/', "_\\1", lcfirst($value)));
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if (($needle != '') && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the string with spaces or underscore in StudlyCaps. 
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function studlycaps($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
    
    /**
     * Returns the portion of the string specified by the start and length parameters.
     * 
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * 
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Generates the letter first of a word in upper.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public static function title($value)
    {
        $value = \ucwords(\strtolower($value));
        
        foreach (['-', '\''] as $delimiter) {
            if (false !== \strpos($value, $delimiter)) {
                $value = \implode($delimiter, \array_map('ucfirst', \explode($delimiter, $value)));
            }
        }
        
        return $value;
    }

    /**
     * Convert the given string to title case in UTF-8 format.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function uTitle($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Replace in the string the spaces by low dashes.
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function underscore($value)
    {
        return strtr($value, ' ', '_');
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value);
    }

    /**
     * Make a string's first character uppercase.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public static function ucfirst($value)
    {
        return static::upper(static::substr($value, 0, 1)).static::substr($value, 1);
    }
}