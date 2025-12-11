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
use ArrayAccess;
use JsonSerializable;
use Stringable as BaseStringable;
use Syscodes\Components\Support\Facades\Date;
use Syscodes\Components\Support\Traits\Macroable;

/**
 * Allows manipulate a string value.
 */
class Stringable implements JsonSerializable, ArrayAccess, BaseStringable
{
    use Macroable;
    
    /**
     * The underlying string value.
     * 
     * @var string
     */
    protected $value;
    
    /**
     * Constructor. Create a new instance of the class.
     * 
     * @param  string  $value
     * 
     * @return void
     */
    public function __construct($value = '')
    {
        $this->value = (string) $value;
    }

     /**
     * Return the remainder of a string after the first occurrence of a given value.
     *
     * @param  string  $search
     * 
     * @return static
     */
    public function after($search): static
    {
        return new static(Str::after($this->value, $search));
    }

    /**
     * Return the remainder of a string after the last occurrence of a given value.
     *
     * @param  string  $search
     * 
     * @return static
     */
    public function afterLast($search): static
    {
        return new static(Str::afterLast($this->value, $search));
    }

    /**
     * Append the given values to the string.
     *
     * @param  array|string  ...$values
     * 
     * @return static
     */
    public function append(...$values): static
    {
        return new static($this->value.implode('', $values));
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param  string  $language
     * 
     * @return static
     */
    public function ascii(): static
    {
        return new static(Str::ascii($this->value));
    }

    /**
     * Get the trailing name component of the path.
     *
     * @param  string  $suffix
     * 
     * @return static
     */
    public function basename($suffix = ''): static
    {
        return new static(basename($this->value, $suffix));
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
     *
     * @param  string  $search
     * 
     * @return static
     */
    public function before($search): static
    {
        return new static(Str::before($this->value, $search));
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
     *
     * @param  string  $search
     * 
     * @return static
     */
    public function beforeLast($search): static
    {
        return new static(Str::beforeLast($this->value, $search));
    }

    /**
     * Get the portion of a string between two given values.
     *
     * @param  string  $from
     * @param  string  $to
     * 
     * @return static
     */
    public function between($from, $to): static
    {
        return new static(Str::between($this->value, $from, $to));
    }

    /**
     * Get the smallest possible portion of a string between two given values.
     *
     * @param  string  $from
     * @param  string  $to
     * 
     * @return static
     */
    public function betweenFirst($from, $to): static
    {
        return new static(Str::betweenFirst($this->value, $from, $to));
    }

    /**
     * Convert a value to camel case.
     *
     * @return static
     */
    public function camel(): static
    {
        return new static(Str::camelcase($this->value));
    }

    /**
     * Get the basename of the class path.
     *
     * @return static
     */
    public function classBasename(): static
    {
        return new static(class_basename($this->value));
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public function contains($needles, $ignoreCase = false)
    {
        return Str::contains($this->value, $needles, $ignoreCase);
    }

    /**
     * Determine if a given string contains all array values.
     *
     * @param  iterable<string>  $needles
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public function containsAll($needles, $ignoreCase = false): bool
    {
        return Str::containsAll($this->value, $needles, $ignoreCase);
    }

    /**
     * Determine if a given string doesn't contain a given substring.
     *
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public function doesntContain($needles, $ignoreCase = false): bool
    {
        return Str::doesntContain($this->value, $needles, $ignoreCase);
    }

    /**
     * Convert the case of a string.
     *
     * @param  int  $mode
     * @param  string|null  $encoding
     * 
     * @return static
     */
    public function convertCase(int $mode = MB_CASE_FOLD, ?string $encoding = 'UTF-8'): static
    {
        return new static(Str::convertCase($this->value, $mode, $encoding));
    }

    /**
     * Get the parent directory's path.
     *
     * @param  int  $levels
     * 
     * @return static
     */
    public function dirname($levels = 1): static
    {
        return new static(dirname($this->value, $levels));
    }

    /**
     * Dump the string.
     *
     * @param  mixed  ...$args
     * 
     * @return static
     */
    public function dump(...$args): static
    {
        dump($this->value, ...$args);

        return $this;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string|iterable<string>  $needles
     * 
     * @return bool
     */
    public function endsWith($needles): bool
    {
        return Str::endsWith($this->value, $needles);
    }

    /**
     * Determine if a given string doesn't end with a given substring.
     *
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public function doesntEndWith($needles)
    {
        return Str::doesntEndWith($this->value, $needles);
    }

    /**
     * Determine if the string is an exact match with the given value.
     *
     * @param  \Syscodes\Components\Support\Stringable|string  $value
     * 
     * @return bool
     */
    public function exactly($value): bool
    {
        if ($value instanceof Stringable) {
            $value = $value->toString();
        }

        return $this->value === $value;
    }

    /**
     * Explode the string into a collection.
     *
     * @param  string  $delimiter
     * @param  int  $limit
     * 
     * @return \Syscodes\Components\Support\Collection<int, string>
     */
    public function explode($delimiter, $limit = PHP_INT_MAX): Collection
    {
        return new Collection(explode($delimiter, $this->value, $limit));
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $cap
     * 
     * @return static
     */
    public function finish($cap): static
    {
        return new static(Str::finish($this->value, $cap));
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|iterable<string>  $pattern
     * @param  bool  $ignoreCase
     * 
     * @return bool
     */
    public function is($pattern, $ignoreCase = false): bool
    {
        return Str::is($pattern, $this->value, $ignoreCase);
    }

    /**
     * Determine if a given string is valid JSON.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return Str::isJson($this->value);
    }

    /**
     * Determine if the given string is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    /**
     * Determine if the given string is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert a string to kebab case.
     *
     * @return static
     */
    public function kebab(): static
    {
        return new static(Str::kebab($this->value));
    }

    /**
     * Make a string's first character lowercase.
     *
     * @return static
     */
    public function lcfirst(): static
    {
        return new static(Str::lcfirst($this->value));
    }

    /**
     * Return the length of the given string.
     *
     * @param  string|null  $encoding
     * 
     * @return int
     */
    public function length($encoding = null): int
    {
        return Str::length($this->value, $encoding);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  int  $limit
     * @param  string  $end
     * @param  bool  $preserveWords
     * 
     * @return static
     */
    public function limit($limit = 100, $end = '...', $preserveWords = false): static
    {
        return new static(Str::limit($this->value, $limit, $end, $preserveWords));
    }

    /**
     * Convert the given string to lower-case.
     *
     * @return static
     */
    public function lower(): static
    {
        return new static(Str::lower($this->value));
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param  string  $pattern
     * 
     * @return static
     */
    public function match($pattern): static
    {
        return new static(Str::match($pattern, $this->value));
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string|iterable<string>  $pattern
     * 
     * @return bool
     */
    public function isMatch($pattern): bool
    {
        return Str::isMatch($pattern, $this->value);
    }

    /**
     * Get the string matching the given pattern.
     *
     * @param  string  $pattern
     * 
     * @return \Syscodes\Support\Collection
     */
    public function matchAll($pattern): Collection
    {
        return Str::matchAll($pattern, $this->value);
    }

    /**
     * Determine if the string matches the given pattern.
     *
     * @param  string  $pattern
     * 
     * @return bool
     */
    public function test($pattern): bool
    {
        return $this->isMatch($pattern);
    }

    /**
     * Append a new line to the string.
     *
     * @param  int  $count
     * 
     * @return static
     */
    public function newLine($count = 1): static
    {
        return $this->append(str_repeat(PHP_EOL, $count));
    }

    /**
     * Remove all non-numeric characters from a string.
     *
     * @return static
     */
    public function numbers(): static
    {
        return new static(Str::numbers($this->value));
    }

    /**
     * Pad both sides of the string with another.
     *
     * @param  int  $length
     * @param  string  $pad
     * 
     * @return static
     */
    public function padBoth($length, $pad = ' '): static
    {
        return new static(Str::padBoth($this->value, $length, $pad));
    }

    /**
     * Pad the left side of the string with another.
     *
     * @param  int  $length
     * @param  string  $pad
     * 
     * @return static
     */
    public function padLeft($length, $pad = ' '): static
    {
        return new static(Str::padLeft($this->value, $length, $pad));
    }

    /**
     * Pad the right side of the string with another.
     *
     * @param  int  $length
     * @param  string  $pad
     * 
     * @return static
     */
    public function padRight($length, $pad = ' '): static
    {
        return new static(Str::padRight($this->value, $length, $pad));
    }

    /**
     * Parse a Class@method style callback into class and method.
     *
     * @param  string|null  $default
     * 
     * @return array<int, string|null>
     */
    public function parseCallback($default = null)
    {
        return Str::parseCallback($this->value, $default);
    }

    /**
     * Call the given callback and return a new string.
     *
     * @param  callable  $callback
     * 
     * @return static
     */
    public function pipe(callable $callback): static
    {
        return new static($callback($this));
    }

    /**
     * Get the plural form of an English word.
     *
     * @param  int|array|\Countable  $count
     * 
     * @return static
     */
    public function plural($count = 2): static
    {
        return new static(Str::plural($this->value, $count));
    }

    /**
     * Find the multi-byte safe position of the first occurrence of the given substring.
     *
     * @param  string  $needle
     * @param  int  $offset
     * @param  string|null  $encoding
     * 
     * @return int|false
     */
    public function position($needle, $offset = 0, $encoding = null): int|false
    {
        return Str::position($this->value, $needle, $offset, $encoding);
    }

    /**
     * Prepend the given values to the string.
     *
     * @param  string  ...$values
     * @return static
     */
    public function prepend(...$values)
    {
        return new static(implode('', $values).$this->value);
    }

    /**
     * Remove any occurrence of the given string in the subject.
     *
     * @param  string|iterable<string>  $search
     * @param  bool  $caseSensitive
     * @return static
     */
    public function remove($search, $caseSensitive = true)
    {
        return new static(Str::remove($search, $this->value, $caseSensitive));
    }

    /**
     * Reverse the string.
     *
     * @return static
     */
    public function reverse(): static
    {
        return new static(Str::reverse($this->value));
    }

    /**
     * Repeat the string.
     *
     * @param  int  $times
     * 
     * @return static
     */
    public function repeat(int $times): static
    {
        return new static(str_repeat($this->value, $times));
    }

    /**
     * Replace the given value in the given string.
     *
     * @param  string|iterable<string>  $search
     * @param  string|iterable<string>  $replace
     * @param  bool  $caseSensitive
     * 
     * @return static
     */
    public function replace($search, $replace, $caseSensitive = true): static
    {
        return new static(Str::replace($search, $replace, $this->value, $caseSensitive));
    }

    /**
     * Replace a given value in the string sequentially with an array.
     *
     * @param  string  $search
     * @param  iterable<string>  $replace
     * 
     * @return static
     */
    public function replaceArray($search, $replace): static
    {
        return new static(Str::replaceArray($search, $replace, $this->value));
    }

    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * 
     * @return static
     */
    public function replaceFirst($search, $replace): static
    {
        return new static(Str::replaceFirst($search, $replace, $this->value));
    }

    /**
     * Replace the first occurrence of the given value if it appears at the start of the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * 
     * @return static
     */
    public function replaceStart($search, $replace): static
    {
        return new static(Str::replaceStart($search, $replace, $this->value));
    }

    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * 
     * @return static
     */
    public function replaceLast($search, $replace): static
    {
        return new static(Str::replaceLast($search, $replace, $this->value));
    }

    /**
     * Replace the last occurrence of a given value if it appears at the end of the string.
     *
     * @param  string  $search
     * @param  string  $replace
     * 
     * @return static
     */
    public function replaceEnd($search, $replace): static
    {
        return new static(Str::replaceEnd($search, $replace, $this->value));
    }

    /**
     * Replace the patterns matching the given regular expression.
     *
     * @param  array|string  $pattern
     * @param  \Closure|string[]|string  $replace
     * @param  int  $limit
     * 
     * @return static
     */
    public function replaceMatches($pattern, $replace, $limit = -1): static
    {
        if ($replace instanceof Closure) {
            return new static(preg_replace_callback($pattern, $replace, $this->value, $limit));
        }

        return new static(preg_replace($pattern, $replace, $this->value, $limit));
    }

    /**
     * Parse input from a string to a collection, according to a format.
     *
     * @param  string  $format
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    public function scan($format): collection
    {
        return new Collection(sscanf($this->value, $format));
    }

    /**
     * Get the singular form of an English word.
     *
     * @return static
     */
    public function singular(): static
    {
        return new static(Str::singular($this->value));
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $separator
     * @param  string|null  $language
     * @param  array<string, string>  $dictionary
     * @return static
     */
    public function slug($separator = '-', $language = 'en', $dictionary = ['@' => 'at'])
    {
        return new static(Str::slug($this->value, $separator, $language, $dictionary));
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $delimiter
     * @return static
     */
    public function snake($delimiter = '_')
    {
        return new static(Str::snake($this->value, $delimiter));
    }

    /**
     * Split a string using a regular expression or by length.
     *
     * @param  string|int  $pattern
     * @param  int  $limit
     * @param  int  $flags
     * 
     * @return \Syscodes\Components\Support\Collection<int, string>
     */
    public function split($pattern, $limit = -1, $flags = 0)
    {
        if (filter_var($pattern, FILTER_VALIDATE_INT) !== false) {
            return new Collection(mb_str_split($this->value, $pattern));
        }

        $segments = preg_split($pattern, $this->value, $limit, $flags);

        return ! empty($segments) ? new Collection($segments) : new Collection;
    }

    /**
     * Determine if a given string doesn't start with a given substring.
     *
     * @param  string|iterable<string>  $needles
     * 
     * @return bool
     */
    public function doesntStartWith($needles): bool
    {
        return Str::doesntStartWith($this->value, $needles);
    }

    /**
     * Convert a value to studly caps case.
     *
     * @return static
     */
    public function studly(): static
    {
        return new static(Str::studlycaps($this->value));
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param  string  $prefix
     * 
     * @return static
     */
    public function start($prefix): static
    {
        return new static(Str::start($this->value, $prefix));
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public function startsWith($needles)
    {
        return Str::startsWith($this->value, $needles);
    }

    /**
     * Strip HTML and PHP tags from the given string.
     *
     * @param  string[]|string|null  $allowedTags
     * 
     * @return static
     */
    public function stripTags($allowedTags = null): static
    {
        return new static(strip_tags($this->value, $allowedTags));
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     *
     * @param  int  $start
     * @param  int|null  $length
     * @param  string  $encoding
     * 
     * @return static
     */
    public function substr($start, $length = null, $encoding = 'UTF-8'): static
    {
        return new static(Str::substr($this->value, $start, $length, $encoding));
    }

    /**
     * Swap multiple keywords in a string with other keywords.
     *
     * @param  array  $map
     * 
     * @return static
     */
    public function swap(array $map): static
    {
        return new static(strtr($this->value, $map));
    }

    /**
     * Take the first or last {$limit} characters.
     *
     * @param  int  $limit
     * 
     * @return static
     */
    public function take(int $limit): static
    {
        if ($limit < 0) {
            return $this->substr($limit);
        }

        return $this->substr(0, $limit);
    }

    /**
     * Convert the given string to proper case.
     *
     * @return static
     */
    public function title(): static
    {
        return new static(Str::title($this->value));
    }

    /**
     * Make a string's first character uppercase.
     *
     * @return static
     */
    public function ucfirst(): static
    {
        return new static(Str::ucfirst($this->value));
    }

    /**
     * Convert the given string to upper-case.
     *
     * @return static
     */
    public function upper(): static
    {
        return new static(Str::upper($this->value));
    }

    /**
     * Get the underlying string value.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->toString();
    }

    /**
     * Get the underlying string value.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Convert the string into a `WebString` instance.
     *
     * @return \Syscodes\Components\Support\WebString
     */
    public function toWebString(): WebString
    {
        return new WebString($this->value);
    }

    /**
     * Convert the string to Base64 encoding.
     *
     * @return static
     */
    public function toBase64(): static
    {
        return new static(base64_encode($this->value));
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  int  $words
     * @param  string  $end
     * 
     * @return static
     */
    public function words($words = 100, $end = '...'): static
    {
        return new static(Str::words($this->value, $words, $end));
    }

    /**
     * Wrap the string with the given strings.
     *
     * @param  string  $before
     * @param  string|null  $after
     * 
     * @return static
     */
    public function wrap($before, $after = null): static
    {
        return new static(Str::wrap($this->value, $before, $after));
    }

    /**
     * Hash the string using the given algorithm.
     *
     * @param  string  $algorithm
     * 
     * @return static
     */
    public function hash(string $algorithm): static
    {
        return new static(hash($algorithm, $this->value));
    }

    /**
     * Encrypt the string.
     *
     * @param  bool  $serialize
     * 
     * @return static
     */
    public function encrypt(bool $serialize = false): static
    {
        return new static(encrypt($this->value, $serialize));
    }

    /**
     * Decrypt the string.
     *
     * @param  bool  $serialize
     * 
     * @return static
     */
    public function decrypt(bool $serialize = false): static
    {
        return new static(decrypt($this->value, $serialize));
    }

     /**
     * Get the underlying string value as an integer.
     *
     * @param  int  $base
     * 
     * @return int
     */
    public function toInteger($base = 10): int
    {
        return intval($this->value, $base);
    }

    /**
     * Get the underlying string value as a float.
     *
     * @return float
     */
    public function toFloat(): float
    {
        return (float) $this->value;
    }

    /**
     * Get the underlying string value as a boolean.
     *
     * @return bool
     */
    public function toBoolean(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get the underlying string value as a Chronos instance.
     *
     * @param  string|null  $format
     * @param  string|null  $tz
     * 
     * @return \Syscodes\Components\Support\Chronos
     *
     * @throws \InvalidException
     */
    public function toDate($format = null, $tz = null)
    {
        if (is_null($format)) {
            return Date::parse($this->value, $tz);
        }

        return Date::createFromFormat($format, $this->value, $tz);
    }

    /**
     * Convert the object to a string when JSON encoded.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  mixed  $offset
     * 
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->value[$offset]);
    }

    /**
     * Get the value at the given offset.
     *
     * @param  mixed  $offset
     * 
     * @return string
     */
    public function offsetGet(mixed $offset): string
    {
        return $this->value[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param  mixed  $offset
     * 
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->value[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  mixed  $offset
     * 
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->value[$offset]);
    }

    /**
     * Magic method.
     * 
     * Proxy dynamic properties onto methods.
     *
     * @param  string  $key
     * 
     * @return mixed
     */
    public function __get($key): mixed
    {
        return $this->{$key}();
    }

    /**
     * Magic method. 
     * 
     * Get the raw string value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}