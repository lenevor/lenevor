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

namespace Syscodes\Components\Support\Traits;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\Facades\Date;
use Syscodes\Components\Support\Stringable;

use function Syscodes\Components\Support\enum_value;

/**
 * Trait InteractWithData.
 */
trait InteractsWithData
{
    /**
     * Retrieve all data from the instance.
     *
     * @param  mixed  $keys
     * @return array
     */
    abstract public function all($keys = null): array;

    /**
     * Retrieve data from the instance.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    abstract protected function data($key = null, $default = null);

    /**
     * Determine if the data contains a given key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Determine if the data contains a given key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        $data = $this->all();

        return array_all($keys, fn ($value) => Arr::has($data, $value));
    }

    /**
     * Determine if the instance contains any of the given keys.
     *
     * @param  string|array  $keys
     * @return bool
     */
    public function hasAny($keys): bool
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $data = $this->all();

        return Arr::hasAny($data, $keys);
    }

    /**
     * Apply the callback if the instance contains the given key.
     *
     * @template TReturn
     * @template TReturnDefault = never
     *
     * @param  string  $key
     * @param  callable(mixed): TReturn  $callback
     * @param  (callable(): TReturnDefault)|null  $default
     * @return $this|TReturn|TReturnDefault
     */
    public function whenHas($key, callable $callback, ?callable $default = null)
    {
        if ($this->has($key)) {
            return $callback(data_get($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Determine if the instance contains a non-empty value for the given key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function filled($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        return array_all($keys, fn ($value) => ! $this->isEmptyString($value));
    }

    /**
     * Determine if the instance contains an empty value for the given key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function isNotFilled($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        return array_all($keys, fn ($value) => $this->isEmptyString($value));
    }

    /**
     * Determine if the instance contains a non-empty value for any of the given keys.
     *
     * @param  string|array  $keys
     * @return bool
     */
    public function anyFilled($keys): bool
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return array_any($keys, fn ($key) => $this->filled($key));
    }

    /**
     * Apply the callback if the instance contains a non-empty value for the given key.
     *
     * @template TReturn
     * @template TReturnDefault = never
     *
     * @param  string  $key
     * @param  callable(mixed): TReturn  $callback
     * @param  (callable(): TReturnDefault)|null  $default
     * @return $this|TReturn|TReturnDefault
     */
    public function whenFilled($key, callable $callback, ?callable $default = null)
    {
        if ($this->filled($key)) {
            return $callback(data_get($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Apply the callback if the instance contains a valid enum value for the given key.
     *
     * @template TEnum of \BackedEnum
     * @template TReturn
     * @template TReturnDefault = never
     *
     * @param  string  $key
     * @param  class-string<TEnum>  $enumClass
     * @param  callable(TEnum):TReturn  $callback
     * @param  (callable(): TReturnDefault)|null  $default
     * @return $this|TReturn|TReturnDefault
     */
    public function whenEnum($key, string $enumClass, callable $callback, ?callable $default = null)
    {
        if ($this->filled($key) && $this->isBackedEnum($enumClass)) {
            $value = $enumClass::tryFrom(data_get($this->all(), $key));

            if ($value !== null) {
                return $callback($value) ?: $this;
            }
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Determine if the instance is missing a given key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function missing($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        return ! $this->has($keys);
    }

    /**
     * Apply the callback if the instance is missing the given key.
     *
     * @template TReturn
     * @template TReturnDefault = never
     *
     * @param  string  $key
     * @param  callable(mixed): TReturn  $callback
     * @param  (callable(): TReturnDefault)|null  $default
     * @return $this|TReturn|TReturnDefault
     */
    public function whenMissing($key, callable $callback, ?callable $default = null)
    {
        if ($this->missing($key)) {
            return $callback(data_get($this->all(), $key)) ?: $this;
        }

        if ($default) {
            return $default();
        }

        return $this;
    }

    /**
     * Determine if the given key is an empty string for "filled".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key): bool
    {
        $value = $this->data($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }

    /**
     * Retrieve data from the instance as a Stringable instance.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return \Syscodes\Components\Support\Stringable
     */
    public function str($key, $default = null)
    {
        return $this->string($key, $default);
    }

    /**
     * Retrieve data from the instance as a Stringable instance.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return \Syscodes\Components\Support\Stringable
     */
    public function string($key, $default = null)
    {
        return new Stringable($this->data($key, $default));
    }

    /**
     * Retrieve data as a boolean value.
     *
     * Returns true when value is "1", "true", "on", and "yes". Otherwise, returns false.
     *
     * @param  string|null  $key
     * @param  bool  $default
     * @return bool
     */
    public function boolean($key = null, $default = false): bool
    {
        return filter_var($this->data($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Retrieve data as an integer value.
     *
     * @param  string  $key
     * @param  int  $default
     * @return int
     */
    public function integer($key, $default = 0): int
    {
        return (int) $this->data($key, $default);
    }

    /**
     * Retrieve data as a float value.
     *
     * @param  string  $key
     * @param  float  $default
     * @return float
     */
    public function float($key, $default = 0.0): float
    {
        return (float) $this->data($key, $default);
    }

    /**
     * Retrieve data from the instance as a Carbon instance.
     *
     * @param  string  $key
     * @param  string|null  $format
     * @param  \UnitEnum|string|null  $tz
     * @return \Syscodes\Components\Support\Chronos|null
     *
     * @throws \Syscodes\Components\Support\Chronos\Exceptions\InvalidFormatException
     */
    public function date($key, $format = null, $tz = null)
    {
        $tz = enum_value($tz);

        if ($this->isNotFilled($key)) {
            return null;
        }

        if (is_null($format)) {
            return Date::parse($this->data($key), $tz);
        }

        return Date::createFromFormat($format, $this->data($key), $tz);
    }

    /**
     * Retrieve data from the instance as an enum.
     *
     * @template TEnum of \BackedEnum
     * @template TDefault of TEnum|null
     *
     * @param  string  $key
     * @param  class-string<TEnum>  $enumClass
     * @param  TDefault  $default
     * @return TEnum|TDefault
     */
    public function enum($key, $enumClass, $default = null)
    {
        if ($this->isNotFilled($key) || ! $this->isBackedEnum($enumClass)) {
            return value($default);
        }

        return $enumClass::tryFrom($this->data($key)) ?: value($default);
    }

    /**
     * Retrieve data from the instance as an array of enums.
     *
     * @template TEnum of \BackedEnum
     *
     * @param  string  $key
     * @param  class-string<TEnum>  $enumClass
     * @return TEnum[]
     */
    public function enums($key, $enumClass)
    {
        if ($this->isNotFilled($key) || ! $this->isBackedEnum($enumClass)) {
            return [];
        }

        return $this->collect($key)
            ->map(fn ($value) => $enumClass::tryFrom($value))
            ->filter()
            ->all();
    }

    /**
     * Determine if the given enum class is backed.
     *
     * @param  class-string  $enumClass
     * @return bool
     */
    protected function isBackedEnum($enumClass): bool
    {
        return is_a($enumClass, \BackedEnum::class, true);
    }

    /**
     * Retrieve data from the instance as an array.
     *
     * @param  array|string|null  $key
     * @return array
     */
    public function array($key = null): array
    {
        return (array) (is_array($key) ? $this->only($key) : $this->data($key));
    }

    /**
     * Retrieve data from the instance as a collection.
     *
     * @param  array|string|null  $key
     * @return \Syscodes\Components\Support\Collection
     */
    public function collect($key = null)
    {
        return new Collection(is_array($key) ? $this->only($key) : $this->data($key));
    }

    /**
     * Get a subset containing the provided keys with values from the instance data.
     *
     * @param  mixed  $keys
     * @return array
     */
    public function only($keys): array
    {
        $results = [];

        $data = $this->all();

        $placeholder = new \stdClass;

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            $value = data_get($data, $key, $placeholder);

            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Get all of the data except for a specified array of items.
     *
     * @param  mixed  $keys
     * @return array
     */
    public function except($keys): array
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    }
}