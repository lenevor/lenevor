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

namespace Syscodes\Components\Contracts\Collection;

use Countable;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\Arrayable;

interface Enumerable extends Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    /**
     * Wrap the given value in a collection if applicable.
     *
     * @template TWrapValue
     *
     * @param  iterable  $value
     * 
     * @return array
     */
    public static function wrap($value);

    /**
     * Get the underlying items from the given collection if applicable.
     *
     * @template TUnwrapKey of array-key
     * @template TUnwrapValue
     *
     * @param  array  $value
     * 
     * @return array
     */
    public static function unwrap($value);

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Convert the object into something JSON serializable.
     * 
     * @return mixed
     */
    public function jsonSerialize(): mixed;

    /**
     * Get the collection of items as JSON.
     *
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson($options = 0): string;

    /**
     * Get the collection of items as pretty print formatted JSON.
     *
     *
     * @param  int  $options
     * 
     * @return string
     */
    public function toPrettyJson(int $options = 0): string;

    /**
     * Get a CachingIterator instance.
     *
     * @param  int  $flags
     * 
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING);

    /**
     * Magic method.
     * 
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Indicate that the model's string representation should be escaped when __toString is invoked.
     *
     * @param  bool  $escape
     * 
     * @return static
     */
    public function escapeWhenCastingToString($escape = true): static;

    /**
     * Add a method to the list of proxied methods.
     * 
     * @param  string  $method
     * 
     * @return void
     */
    public static function proxy($method): void;

    /**
     * Magic method.
     * 
     * Dynamically access collection proxies.
     *
     * @param  string  $key
     * 
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get($key): mixed;
}