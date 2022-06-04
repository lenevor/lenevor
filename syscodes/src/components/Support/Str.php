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

namespace Syscodes\Components\Support;

use Syscodes\Components\Collections\Arr;

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
     * The cache of snake-cased words.
     * 
     * @var array $snakeCache
     */
    protected static $snakeCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array $studlyCache
     */
    protected static $studlyCache = [];
    
    /**
     * Return the remainder of a string after the first occurrence of a given value.
     * 
     * @param  string  $subject
     * @param  string  $search
     * 
     * @return string
     */
    public static function after($subject, $search): string
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public static function ascii($value): string
    {
        return str_replace('/[^\x20-\x7E]/u', '', $value);
    }
    
    /**
     * Get the portion of a string before the first occurrence of a given value.
     * 
     * @param  string  $subject
     * @param  string  $search
     * 
     * @return string
     */
    public static function before($subject, $search): string
    {
        if ($search === '') {
            return $subject;
        }
        
        $result = strstr($subject, (string) $search, true);
        
        return $result === false ? $subject : $result;
    }

    /**
     * Convert the string with spaces or underscore in camelcase notation.
     *
     * @param  string  $value  String to convert
     *
     * @return string
     */
    public static function camelcase($value): string
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
    public static function contains($haystack, $needles): bool
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
    public static function endsWith($haystack, $needles): bool
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
    public static function dash($value): string
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
    public static function humanize($value): string
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
    public static function is($pattern, $value): bool
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
     * Convert a string to kebab case.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public static function kebab($value): string
    {
        return static::snake('value', '-');
    }

    /**
     * Return the length of the given string.
     *
     * @param  string  $value  String to length
     * @param  string|null  $encoding  String encoding
     * 
     * @return int
     */
    public static function length($value, $encoding = null): int
    {
        if ($encoding) {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     *
     * @return string
     *
     * @uses   \Syscodes\Components\Support\Str::length
     */
    public static function limit($value, $limit, $end = '...'): string
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
    public static function lower($value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }
    
    /**
     * Pad both sides with the length of another.
     * 
     * @param  string  $value
     * @param  int  $padLength
     * @param  string  $padString
     * 
     * @return string 
     */
    public static function padBoth(string $value, int $padLength, string $padString = ' '): string 
    {
        return str_pad($value, $padLength, $padString, STR_PAD_BOTH);
    }

    /**
     * Pad the left side with the length of another.
     * 
     * @param  string  $value
     * @param  int  $padLength
     * @param  string  $padString
     * 
     * @return string 
     */
    public static function padLeft(string $value, int $padLength, string $padString = ' '): string 
    {
        return str_pad($value, $padLength, $padString, STR_PAD_LEFT);
    }

    /**
     * Pad the left side with the length of another.
     * 
     * @param  string  $value
     * @param  int  $padLength
     * @param  string  $padString
     * 
     * @return string 
     */
    public static function padRight(string $value, int $padLength, string $padString = ' '): string 
    {
        return str_pad($value, $padLength, $padString, STR_PAD_RIGHT);
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
    public static function parseCallback($callback, $default = null): array
    {
        return static::contains($callback, '@') ? explode('@', ucfirst($callback), 2) : [$callback, $default];
    }

    /**
     * Get the plural form of an English word.
     * 
     * @param  string  $value
     * @param  int|array|\Countable  $count
     * 
     * @return string 
     */
    public static function plural($value, $count = 2): string
    {
        return (new Inflector)->pluralize($value, $count);
    }
    
    /**
     * Generate a more truly "random" alpha-numeric string.
     * 
     * @param  int  $length  
     * 
     * @return string
     */
    public static function random($length = 16): string
    {
        $string = '';
        
        while (($len = strlen($string)) < $length) {
            $size    = $length - $len;
            $bytes   = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        
        return $string;
    }

    /**
     * Repeat the given string.
     * 
     * @param  string  $value
     * @param  int  $times
     * 
     * @return string
     */
    public static function repeat($value, $times): string
    {
        return str_repeat($value, $times);
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
    public static function replaceArray($search, array $replace, $subject): string
    {
        $segments = explode($search, $subject);
        
        $result = array_shift($segments);
        
        foreach ($segments as $segment) {
            $result .= (array_shift($replace) ?? $search).$segment;
        }
        
        return $result;
    }

    /**
     * Replace the given value in the given string.
     * 
     * @param  string|string[]  $search
     * @param  string|string[]  $replace
     * @param  string|string[]  $subject
     * 
     * @return string
     */
    public static function replace($search, $replace, $subject): string
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Remove any occurrence of the given string in the subject.
     * 
     * @param  string|string[]  $search
     * @param  string|string[]  $subject
     * @param  bool  $caseReplace
     * 
     * @param  string
     */
    public static function remove($search, $subject, bool $caseReplace = true)
    {
        $subject = $caseReplace
                    ? str_replace($search, '', $subject)
                    : str_ireplace($search, '', $subject);
        
        return $subject;
    }

    /**
     * Get the singular form of an English word.
     * 
     * @param  string  $value
     * 
     * @return string 
     */
    public static function singular($value): string
    {
        return (new Inflector)->singular($value);
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     * 
     * @param  string  $title
     * @param  string  $separator
     * 
     * @return string
     */
    public static function slug($title, $separator = '-'): string
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
    public static function smallcase($value): string
    {
        return mb_strtolower(preg_replace('/([A-Z])/', "_\\1", lcfirst($value)));
    }

    /**
     * Convert a string to snake case.
     * 
     * @param  string  $value
     * @param  string  $delimiter
     * 
     * @return string
     */
    public static function snake($value, $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if ( ! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     *
     * @return bool
     */
    public static function startsWith($haystack, $needles): bool
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
    public static function studlycaps($value): string
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
    public static function substr($string, $start, $length = null): string
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
    public static function title($value): string
    {
        $value = ucwords(strtolower($value));
        
        foreach (['-', '\''] as $delimiter) {
            if (false !== strpos($value, $delimiter)) {
                $value = implode($delimiter, array_map('ucfirst', explode($delimiter, $value)));
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
    public static function uTitle($value): string
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
    public static function underscore($value): string
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
    public static function upper($value): string
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
    public static function ucfirst($value): string
    {
        return static::upper(static::substr($value, 0, 1)).static::substr($value, 1);
    }
}