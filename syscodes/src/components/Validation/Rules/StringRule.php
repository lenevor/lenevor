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

namespace Syscodes\Components\Validation\Rules;

use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Traits\Conditionable;
use Stringable;

/**
 * Gets the attribute must be string rule.
 */
class StringRule implements Stringable
{
    use Conditionable;

    /**
     * The constraints for the string rule.
     */
    protected array $constraints = ['string'];

    /**
     * The field under validation must be entirely alphabetic characters.
     *
     * @param  bool  $ascii
     * @return static
     */
    public function alpha(bool $ascii = false): static
    {
        return $this->addRule($ascii ? 'alpha:ascii' : 'alpha');
    }

    /**
     * The field under validation must be entirely alpha-numeric characters, dashes, and underscores.
     *
     * @param  bool  $ascii
     * @return static
     */
    public function alphaDash(bool $ascii = false): static
    {
        return $this->addRule($ascii ? 'alpha_dash:ascii' : 'alpha_dash');
    }

    /**
     * The field under validation must be entirely alpha-numeric characters.
     *
     * @param  bool  $ascii
     * @return static
     */
    public function alphaNumeric(bool $ascii = false): static
    {
        return $this->addRule($ascii ? 'alpha_num:ascii' : 'alpha_num');
    }

    /**
     * The field under validation must be entirely ASCII characters.
     *
     * @return static
     */
    public function ascii(): static
    {
        return $this->addRule('ascii');
    }

    /**
     * The field under validation must have a length between the given min and max (inclusive).
     *
     * @param  int  $min
     * @param  int  $max
     * @return static
     */
    public function between(int $min, int $max): static
    {
        return $this->addRule('between:'.$min.','.$max);
    }

    /**
     * The field under validation must not end with any of the given values.
     *
     * @param  string  ...$values
     * @return static
     */
    public function doesntEndWith(string ...$values): static
    {
        return $this->addRule('doesnt_end_with:'.implode(',', $values));
    }

    /**
     * The field under validation must not start with any of the given values.
     *
     * @param  string  ...$values
     * @return static
     */
    public function doesntStartWith(string ...$values): static
    {
        return $this->addRule('doesnt_start_with:'.implode(',', $values));
    }

    /**
     * The field under validation must end with one of the given values.
     *
     * @param  string  ...$values
     * @return static
     */
    public function endsWith(string ...$values): static
    {
        return $this->addRule('ends_with:'.implode(',', $values));
    }

    /**
     * The field under validation must have an exact length.
     *
     * @param  int  $value
     * @return static
     */
    public function exactly(int $value): static
    {
        return $this->addRule('size:'.$value);
    }

    /**
     * The field under validation must be entirely lowercase.
     *
     * @return static
     */
    public function lowercase(): static
    {
        return $this->addRule('lowercase');
    }

    /**
     * The field under validation must not exceed the given length.
     *
     * @param  int  $value
     * @return static
     */
    public function max(int $value): static
    {
        return $this->addRule('max:'.$value);
    }

    /**
     * The field under validation must have a minimum length.
     *
     * @param  int  $value
     * @return static
     */
    public function min(int $value): static
    {
        return $this->addRule('min:'.$value);
    }

    /**
     * The field under validation must start with one of the given values.
     *
     * @param  string  ...$values
     * @return static
     */
    public function startsWith(string ...$values): static
    {
        return $this->addRule('starts_with:'.implode(',', $values));
    }

    /**
     * The field under validation must be entirely uppercase.
     *
     * @return static
     */
    public function uppercase(): static
    {
        return $this->addRule('uppercase');
    }

    /**
     * Magic method.
     * 
     * Convert the rule to a validation string.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return implode('|', array_unique($this->constraints));
    }

    /**
     * Add custom rules to the validation rules array.
     * 
     * @param  array|string  $rules
     * @return static
     */
    protected function addRule(array|string $rules): static
    {
        $this->constraints = array_merge($this->constraints, Arr::wrap($rules));

        return $this;
    }
}