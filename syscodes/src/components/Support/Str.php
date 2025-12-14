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

namespace Syscodes\Components\Support;

use Closure;
use Throwable;
use Traversable;

/**
 * Allows convert a string in diferentes modes of text presentation, either, 
 * camel-cased, studlycaps and replace characters in a string.
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
     * Return the remainder of a string after the last occurrence of a given value.
     * 
     * @param  string  $subject
     * @param  string  $search
     * 
     * @return string
     */
    public static function afterLast($subject, $search): string
    {
        if ($search === '') {
            return $subject;
        }
        
        $position = strrpos($subject, (string) $search);
        
        if ($position === false) {
            return $subject;
        }
        
        return substr($subject, $position + strlen($search));
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
     * Get the portion of a string before the last occurrence of a given value.
     * 
     * @param  string  $subject
     * @param  string  $search
     * 
     * @return string
     */
    public static function beforeLast($subject, $search): string
    {
        if ($search === '') {
            return $subject;
        }
        
        $pos = mb_strrpos($subject, $search);
        
        if ($pos === false) {
            return $subject;
        }
        
        return static::substr($subject, 0, $pos);
    }

    /**
     * Get the portion of a string between two given values.
     *
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * 
     * @return string
     */
    public static function between($subject, $from, $to): string
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::beforeLast(static::after($subject, $from), $to);
    }

    /**
     * Get the smallest possible portion of a string between two given values.
     *
     * @param  string  $subject
     * @param  string  $from
     * @param  string  $to
     * 
     * @return string
     */
    public static function betweenFirst($subject, $from, $to): string
    {
        if ($from === '' || $to === '') {
            return $subject;
        }

        return static::before(static::after($subject, $from), $to);
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
     * Get the character at the specified index.
     * 
     * @param  string  $subject
     * @param  int  $index
     * 
     * @return string|false
     */
    public static function charAt($subject, $index): string|false
    {
        $length = mb_strlen($subject);
        
        if ($index < 0 ? $index < -$length : $index > $length - 1) {
            return false;
        }
        
        return mb_substr($subject, $index, 1);
    }

    /**
     * Determine if a given string contains a given substring.
     * 
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public static function contains($haystack, $needles, $ignoreCase = false): bool
    {
        if (is_null($haystack)) {
            return false;
        }

        if ($ignoreCase) {
            $haystack = mb_strtolower($haystack);
        }

        if ( ! is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ($ignoreCase) {
                $needle = mb_strtolower($needle);
            }

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string contains all array values.
     *
     * @param  string  $haystack
     * @param  iterable<string>  $needles
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public static function containsAll($haystack, $needles, $ignoreCase = false): bool
    {
        foreach ($needles as $needle) {
            if ( ! static::contains($haystack, $needle, $ignoreCase)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if a given string doesn't contain a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public static function doesntContain($haystack, $needles, $ignoreCase = false): bool
    {
        return ! static::contains($haystack, $needles, $ignoreCase);
    }

    /**
     * Convert the case of a string.
     *
     * @param  string  $string
     * @param  int  $mode
     * @param  string|null  $encoding
     * 
     * @return string
     */
    public static function convertCase(string $string, int $mode = MB_CASE_FOLD, ?string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($string, $mode, $encoding);
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
        if (is_null($haystack)) {
            return false;
        }

        if (! is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string doesn't end with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * 
     * @return bool
     */
    public static function doesntEndWith($haystack, $needles): bool
    {
        return ! static::endsWith($haystack, $needles);
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
     * Cap a string with a single instance of a given value.
     * 
     * @param  string  $value
     * @param  string  $cap
     * 
     * @return string
     */
    public static function finish($value, $cap): string
    {
        $quoted = preg_quote($cap, '/');
        
        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
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
     * Determine if a given value is valid JSON.
     *
     * @param  mixed  $value
     * 
     * @return bool
     */
    public static function isJson($value): bool
    {
        if ( ! is_string($value)) {
            return false;
        }

        return json_validate($value, 512);
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
        return static::snake($value, '-');
    }

    /**
     * Make a string's first character lowercase.
     *
     * @param  string  $string
     * 
     * @return string
     */
    public static function lcfirst($string): string
    {
        return static::lower(static::substr($string, 0, 1)).static::substr($string, 1);
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
     * @param  bool  $preserveWords
     *
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...', $preserveWords = false): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) return $value;

        if ( ! $preserveWords) {
            return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
        }

        $value = trim(preg_replace('/[\n\r]+/', ' ', strip_tags($value)));

        $trimWidth = rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8'));

        if (mb_substr($value, $limit, 1, 'UTF-8') === ' ') {
            return $trimWidth.$end;
        }

        return preg_replace("/(.*)\s.*/", '$1', $trimWidth).$end;
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
     * Get the string matching the given pattern.
     *
     * @param  string  $pattern
     * @param  string  $subject
     * 
     * @return string
     */
    public static function match($pattern, $subject): string
    {
        preg_match($pattern, $subject, $matches);

        if ( ! $matches) {
            return '';
        }

        return $matches[1] ?? $matches[0];
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|iterable<string>  $pattern
     * @param  string  $value
     * 
     * @return bool
     */
    public static function isMatch($pattern, $value): bool
    {
        $value = (string) $value;

        if ( ! is_iterable($pattern)) {
            $pattern = [$pattern];
        }

        foreach ($pattern as $pattern) {
            $pattern = (string) $pattern;

            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param  string  $pattern
     * @param  string  $subject
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    public static function matchAll($pattern, $subject): Collection
    {
        preg_match_all($pattern, $subject, $matches);

        if (empty($matches[0])) {
            return new Collection;
        }

        return new Collection($matches[1] ?? $matches[0]);
    }

    /**
     * Remove all non-numeric characters from a string.
     *
     * @param  string  $value
     * 
     * @return string
     */
    public static function numbers($value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Get a new stringable object from the given string.
     *
     * @param  string  $string
     * 
     * @return \Syscodes\Components\Support\Stringable
     */
    public static function of($string): Stringable
    {
        return new Stringable($string);
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
        return mb_str_pad($value, $padLength, $padString, STR_PAD_BOTH);
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
        return mb_str_pad($value, $padLength, $padString, STR_PAD_LEFT);
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
        return mb_str_pad($value, $padLength, $padString, STR_PAD_RIGHT);
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
     * Find the multi-byte safe position of the first occurrence of a given substring in a string.
     *
     * @param  string  $haystack
     * @param  string  $needle
     * @param  int  $offset
     * @param  string|null  $encoding
     * 
     * @return int|false
     */
    public static function position($haystack, $needle, $offset = 0, $encoding = null): int|false
    {
        return mb_strpos($haystack, (string) $needle, $offset, $encoding);
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
        return (function ($length) {

            $string = '';
            
            while (($len = strlen($string)) < $length) {
                $size    = $length - $len;

                $bytesSize = (int) ceil($size / 3) * 3;

                $bytes   = random_bytes($bytesSize);

                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }
            
            return $string;
        })($length);
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
     * Replace the given value in the given string.
     * 
     * @param  string|iterable<string>  $search
     * @param  string|iterable<string>  $replace
     * @param  string|iterable<string>  $subject
     * @param  bool  $caseSensitive
     * 
     * @return ($subject is string ? string : string[])
     */
    public static function replace($search, $replace, $subject, $caseSensitive = true): string
    {
        if ($search instanceof Traversable) {
            $search = Arr::from($search);
        }

        if ($replace instanceof Traversable) {
            $replace = Arr::from($replace);
        }

        if ($subject instanceof Traversable) {
            $subject = Arr::from($subject);
        }
        
        return $caseSensitive 
            ? str_replace($search, $replace, $subject)
            : str_ireplace($search, $replace, $subject);
    }

    /**
     * Replace a given value in the string sequentially with an array.
     * 
     * @param  string  $search
     * @param  string[]  $replace
     * @param  string  $subject
     * 
     * @return string
     */
    public static function replaceArray($search, $replace, $subject): string
    {
        if ($replace instanceof Traversable) {
            $replace = Arr::from($replace);
        }

        $segments = explode($search, $subject);

        $result = array_shift($segments);

        foreach ($segments as $segment) {
            $result .= self::toStringOr(array_shift($replace) ?? $search, $search).$segment;
        }

        return $result;
    }

    /**
     * Convert the given value to a string or return the given fallback on failure.
     *
     * @param  mixed  $value
     * @param  string  $fallback
     * 
     * @return string
     */
    protected static function toStringOr($value, $fallback): string
    {
        try {
            return (string) $value;
        } catch (Throwable $e) {
            return $fallback;
        }
    }
    
    /**
     * Replace the first occurrence of a given value in the string.
     * 
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * 
     * @return string
     */
    public static function replaceFirst($search, $replace, $subject): string
    {
        $search = (string) $search;

        if ($search == '') {
            return $subject;
        }
        
        $position = strpos($subject, $search);
        
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        
        return $subject;
    }

    /**
     * Replace the first occurrence of the given value if it appears at the start of the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * 
     * @return string
     */
    public static function replaceStart($search, $replace, $subject): string
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        if (static::startsWith($subject, $search)) {
            return static::replaceFirst($search, $replace, $subject);
        }

        return $subject;
    }
    
    /**
     * Replace the last occurrence of a given value in the string.
     * 
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * 
     * @return string
     */
    public static function replaceLast($search, $replace, $subject): string
    {   
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }
        
        $position = strrpos($subject, $search);
        
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        
        return $subject;
    }

    /**
     * Replace the last occurrence of a given value if it appears at the end of the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $subject
     * 
     * @return string
     */
    public static function replaceEnd($search, $replace, $subject): string
    {
        $search = (string) $search;

        if ($search === '') {
            return $subject;
        }

        if (static::endsWith($subject, $search)) {
            return static::replaceLast($search, $replace, $subject);
        }

        return $subject;
    }

    /**
     * Replace the patterns matching the given regular expression.
     *
     * @param  array|string  $pattern
     * @param  \Closure|string[]|string  $replace
     * @param  array|string  $subject
     * @param  int  $limit
     * 
     * @return string|string[]|null
     */
    public static function replaceMatches($pattern, $replace, $subject, $limit = -1)
    {
        if ($replace instanceof Closure) {
            return preg_replace_callback($pattern, $replace, $subject, $limit);
        }

        return preg_replace($pattern, $replace, $subject, $limit);
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
        if ($search instanceof Traversable) {
            $search = Arr::from($search);
        }

        return $caseReplace
            ? str_replace($search, '', $subject)
            : str_ireplace($search, '', $subject);
    }

    /**
     * Reverse the given string.
     *
     * @param  string  $value
     * 
     * @return string
     */
    public static function reverse(string $value): string
    {
        return implode(array_reverse(mb_str_split($value)));
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
     * Begin a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $prefix
     * 
     * @return string
     */
    public static function start($value, $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
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
        if (is_null($haystack)) {
            return false;
        }
        
        if ( ! is_iterable($needles)) {
            $needles = [$needles];
        }
        
        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Determine if a given string doesn't start with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * 
     * @return bool
     */
    public static function doesntStartWith($haystack, $needles): bool
    {
        return ! static::startsWith($haystack, $needles);
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

        $words = mb_split('\s+', static::replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn ($word) => static::ucfirst($word), $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }
    
    /**
     * Returns the portion of the string specified by the start and length parameters.
     * 
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @param  string  $encoding
     * 
     * @return string
     */
    public static function substr($string, $start, $length = null, $encoding = 'UTF-8'): string
    {
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * Take the first or last {$limit} characters of a string.
     *
     * @param  string  $string
     * @param  int  $limit
     * 
     * @return string
     */
    public static function take($string, int $limit): string
    {
        if ($limit < 0) {
            return static::substr($string, $limit);
        }

        return static::substr($string, 0, $limit);
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
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert the given string to Base64 encoding.
     *
     * @param  string  $string
     * 
     * @return string
     */
    public static function toBase64($string): string
    {
        return base64_encode($string);
    }

    /**
     * Decode the given Base64 encoded string.
     *
     * @param  string  $string
     * @param  bool  $strict
     * 
     * @return string|false
     */
    public static function fromBase64($string, $strict = false): string|false
    {
        return base64_decode($string, $strict);
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
        return mb_strtoupper($value, 'UTF-8');
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
    
    /**
     * Unwrap the string with the given strings.
     *
     * @param  string  $value
     * @param  string  $before
     * @param  string|null  $after
     * 
     * @return string
     */
    public static function unwrap($value, $before, $after = null): string
    {
        if (static::startsWith($value, $before)) {
            $value = static::substr($value, static::length($before));
        }

        if (static::endsWith($value, $after ??= $before)) {
            $value = static::substr($value, 0, -static::length($after));
        }

        return $value;
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  string  $value
     * @param  int  $words
     * @param  string  $end
     * 
     * @return string
     */
    public static function words($value, $words = 100, $end = '...'): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if ( ! isset($matches[0]) || static::length($value) === static::length($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }
    
    /**
     * Wrap the string with the given strings.
     *
     * @param  string  $value
     * @param  string  $before
     * @param  string|null  $after
     * 
     * @return string
     */
    public static function wrap($value, $before, $after = null): string
    {
        return $before.$value.($after ?? $before);
    }

    /**
     * Remove all strings from the casing caches.
     *
     * @return void
     */
    public static function flushCache(): void
    {
        static::$snakeCache = [];
        static::$camelCache = [];
        static::$studlyCache = [];
    }
}