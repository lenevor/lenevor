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

use Syscodes\Components\Contracts\Support\Webable;
use Syscodes\Components\Support\Environment;
use Syscodes\Components\Support\HigherOrderTakeProxy;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Version;
use Syscodes\Components\Support\Interval;


if ( ! function_exists('blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param  mixed  $value
     * 
     * @return bool
     */
    function blank($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        if ($value instanceof Stringable) {
            return trim((string) $value) === '';
        }

        return empty($value);
    }
}

if ( ! function_exists('camel_case')) {
    /**
     * Convert the string with spaces or underscore in camelcase notation.
     *
     * @param  string  $string  
     *
     * @return string
     */
    function camel_case($string): string
    {
        return Str::camelcase($string);
    }
}

if ( ! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * 
     * @return string
     */
    function class_basename($class): string
    {
        $className = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $className));
    }
}

if ( ! function_exists('class_recursive'))
{
    /**
     * Returns all traits used by a class, it's subclasses and trait of their traits
     * 
     * @param  string  $class
     * 
     * @return array
     */
    function class_recursive($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class) ?: []) + [$class => $class] as $class) {
            $results += trait_recursive($class);
        }
        
        return array_unique($results);
    }
}

if ( ! function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param  string  $value
     * @param  bool  $doubleEncode
     *
     * @return string
     */
    function e($value, $doubleEncode = true): string
    {
        if ($value instanceof Webable) {
            return $value->toHtml() ?? '';
        }

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }
}

if ( ! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     * 
     * @param  string  $key
     * @param  mixed  $default  
     * 
     * @return mixed
     */
    function env($key, $default = null)
    {
        return Environment::get($key, $default);
    }
}

if ( ! function_exists('filled')) {
    /**
     * Determine if a value is "filled".
     *
     * @param  mixed  $value
     * 
     * @return bool
     */
    function filled($value): bool
    {
        return ! blank($value);
    }
}

if ( ! function_exists('preg_replace_sub')) {
    /**
     * Replace a given pattern with each value in the array in sequentially.
     * 
     * @param  string  $pattern
     * @param  array   $replacements
     * @param  string  $subject
     * 
     * @return string
     */
    function preg_replace_sub($pattern, &$replacements, $subject)
    {
        return preg_replace_callback($pattern, fn ($match) => array_shift($replacements), $subject);
    }
}

if ( ! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param  int  $times
     * @param  callable  $callback
     * @param  int|\Closure  $sleepMilliseconds
     * @param  callable|null  $when
     * 
     * @return mixed
     *
     * @throws \Exception
     */
    function retry($times, callable $callback, $sleepMilliseconds = 0, $when = null)
    {
        $attempts = 0;

        $backoff = [];

        if (is_array($times)) {
            $backoff = $times;

            $times = count($times) + 1;
        }

        beginning:
        $attempts++;
        $times--;

        try {
            return $callback($attempts);
        } catch (Exception $e) {
            if ($times < 1 || ($when && ! $when($e))) {
                throw $e;
            }

            $sleepMilliseconds = $backoff[$attempts - 1] ?? $sleepMilliseconds;

            if ($sleepMilliseconds) {
                Interval::usleep(value($sleepMilliseconds, $attempts, $e) * 1000);
            }

            goto beginning;
        }
    }
}

if ( ! function_exists('str_dash')) {
    /**
     * Replace in the chain the spaces by dashes.
     *
     * @param  string  $string  
     *
     * @return string
     */
    function str_dash($string): string
    {
        return Str::dash($string);
    }
}

if ( ! function_exists('str_humanize')) {
    /**
     * Replace in an string the underscore or dashed by spaces.
     *
     * @param  string  $string
     *
     * @return string
     */
    function str_humanize($string): string
    {
        return Str::humanize($string);
    }
}

if ( ! function_exists('str_smallcase')) {
    /**
     * Converts the CamelCase string into smallcase notation.
     *
     * @param  string  $string
     *
     * @return string
     */
    function str_smallcase($string): string
    {
        return Str::smallcase($string);
    }
}

if ( ! function_exists('str_underscore')) {
    /**
     * Replace in the string the spaces by low dashes.
     *
     * @param  string  $string
     *
     * @return string
     */
    function str_underscore($string): string
    {
        return Str::underscore($string);
    }
}

if ( ! function_exists('studly_caps')) {
    /**
     * Convert the string with spaces or underscore in StudlyCaps. 
     *
     * @param  string  $string
     *
     * @return string
     */
    function studly_caps($string): string
    {
        return Str::studlycaps($string);
    }
}

if ( ! function_exists('take')) {
    /**
     * Call the given Closure if this activated then return the value.
     * 
     * @param  mixed  $value
     * @param  \Closure|null  $callback
     * 
     * @return mixed
     * 
     * @uses   \Syscodes\Components\Support\HigherOrderTakeProxy
     */
    function take(mixed $value, ?\Closure $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTakeProxy($value);
        }

        $callback($value);

        return $value;
    }
}

if ( ! function_exists('title')) {
    /**
     * Generates the letter first of a word in upper.
     * 
     * @param  string  $string
     * 
     * @return string
     */
    function title($string): string
    {
        return Str::title($string);
    }
}

if ( ! function_exists('trait_recursive'))
{
    /**
     * Returns all traits used by a trait and its traits.
     * 
     * @param  string  $trait
     * 
     * @return array
     */
    function trait_recursive($trait): array
    {
        $traits = class_uses($trait) ?: [];
        
        foreach ($traits as $trait) {
            $traits += trait_recursive($trait);
        }
        
        return $traits;
    }
}

if ( ! function_exists('transform')) {
    /**
     * Transform the given value if it is present.
     *
     * @template TValue
     * @template TReturn
     * @template TDefault
     *
     * @param  mixed  $value
     * @param  callable  $callback
     * @param  callable  $default
     * 
     * @return mixed
     */
    function transform($value, callable $callback, $default = null)
    {
        if (filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}

if ( ! function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true.
     *
     * @param  mixed  $condition
     * @param  \Closure|string|object  $exception
     * @param  Array ...$parameters
     * 
     * @return mixed
     *
     * @throws \RuntimeException
     */
    function throw_if($condition, $exception = 'RuntimeException', ...$parameters)
    {
        if ($condition) {
            if ($exception instanceof Closure) {
                $exception = $exception(...$parameters);
            }

            if (is_string($exception) && class_exists($exception)) {
                $exception = new $exception(...$parameters);
            }

            throw is_string($exception) ? new RuntimeException($exception) : $exception;
        }

        return $condition;
    }
}

if ( ! function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true.
     *
     * @param  mixed  $condition
     * @param  \Closure|string|object  $exception
     * @param  array  ...$parameters
     * 
     * @return mixed
     *
     * @throws \RuntimeException
     */
    function throw_unless($condition, $exception = 'RuntimeException', ...$parameters)
    {
        throw_if( ! $condition, $exception, ...$parameters);

        return $condition;
    }
}

if ( ! function_exists('version')) {
    /**
     * Return number version of the Lenevor.
     * 
     * @return string
     */
    function version(): string
    {
        return Version::RELEASE.'-'.Version::STATUS;
    }
}

if ( ! function_exists('win_os')) {
    /**
     * Determine whether the current envrionment is Windows based.
     *
     * @return bool
     */
    function win_os(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}

if ( ! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     * 
     * @param  mixed  $value
     * @param  \callable|null  $callback
     * 
     * @return mixed
     */
    function with($value, ?callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}