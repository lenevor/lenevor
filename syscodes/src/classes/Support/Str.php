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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Support;

/**
 * Allows convert a string in diferentes modes of text presentation, either, 
 * camel-cased, studlycaps and replace characters in a string.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
        if (isset(static::$camelCache[$value]))
        {
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
        foreach ((array) $needles as $needle)
        {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false)
            {
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
        foreach ((array) $needles as $needle)
        {
            if (substr($haystack, -strlen($needle)) === (string) $needle)
            {
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

        if (is_null($patterns))
        {
            return false;
        }

        foreach ($patterns as $pattern)
        {
            if ($pattern == $value)
            {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translate into regular expression wildcards to verify a string
            $pattern = str_replace('\*', '.*', $pattern).'\z';

            if (preg_match('#^'.$pattern.'#u', $value) === 1)
            {
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
     * Parse a Class@method style callback into class and method.
     * Puts the class name with the first capital letter.
     * 
     * @param  string       $callback
     * @param  string|null  $default   (null by default)
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
     * @param  int  $length  (16 by default)
     * 
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';
        
        while (($len = strlen($string)) < $length)
        {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        
        return $string;
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
        foreach ((array) $needles as $needle) 
        {
            if (($needle != '') && substr($haystack, 0, strlen($needle)) === (string) $needle)
            {
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

        if (isset(static::$studlyCache[$key]))
        {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string  $value
     *
     * @return string
     */
    public static function title($value)
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
}