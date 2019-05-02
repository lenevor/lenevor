<?php

namespace Syscode\Support;

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2018-2019 Lenevor PHP Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
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
     * Determine if a given string ends with a given substring.
     *
     * @param  string        $haystack
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
     * @param  int     $limit
     * @param  string  $end
     *
     * @return tring
     *
     * @uses   \Syscode\Support\Str::length
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
     * @param  string        $haystack
     * @param  string|array  $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ($needles as $needle) 
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