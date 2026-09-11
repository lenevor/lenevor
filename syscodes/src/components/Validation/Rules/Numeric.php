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
 * Gets the attribute must be numeric.
 */
class Numeric implements Stringable
{
    use Conditionable;

    /**
     * The constraints for the number rule.
     */
    protected array $constraints = ['numeric'];

    /**
     * The field under validation must have a size between the given min and max (inclusive).
     *
     * @param  int|float  $min
     * @param  int|float  $max
     * @return static
     */
    public function between(int|float $min, int|float $max): static
    {
        return $this->addRule('between:'.$min.','.$max);
    }

    /**
     * The field under validation must contain the specified number of decimal places.
     *
     * @param  int  $min
     * @param  int|null  $max
     * @return static
     */
    public function decimal(int $min, ?int $max = null): static
    {
        $rule = 'decimal:'.$min;

        if ($max !== null) {
            $rule .= ','.$max;
        }

        return $this->addRule($rule);
    }

    /**
     * The field under validation must have a different value than field.
     *
     * @param  string  $field
     * @return static
     */
    public function different(string $field): static
    {
        return $this->addRule('different:'.$field);
    }

    /**
     * The integer under validation must have an exact number of digits.
     *
     * @param  int  $length
     * @return static
     */
    public function digits(int $length): static
    {
        return $this->integer()->addRule('digits:'.$length);
    }

    /**
     * The integer under validation must between the given min and max number of digits.
     *
     * @param  int  $min
     * @param  int  $max
     * @return static
     */
    public function digitsBetween(int $min, int $max): static
    {
        return $this->integer()->addRule('digits_between:'.$min.','.$max);
    }

    /**
     * The field under validation must be greater than the given field or value.
     *
     * @param  string  $field
     * @return static
     */
    public function greaterThan(string $field): static
    {
        return $this->addRule('gt:'.$field);
    }

    /**
     * The field under validation must be greater than or equal to the given field or value.
     *
     * @param  string  $field
     * @return static
     */
    public function greaterThanOrEqualTo(string $field): static
    {
        return $this->addRule('gte:'.$field);
    }

    /**
     * The field under validation must be an integer.
     *
     * @return static
     */
    public function integer(bool $strict = false): static
    {
        return $this->addRule($strict ? 'integer:strict' : 'integer');
    }

    /**
     * The field under validation must be less than the given field.
     *
     * @param  string  $field
     * @return static
     */
    public function lessThan(string $field): static
    {
        return $this->addRule('lt:'.$field);
    }

    /**
     * The field under validation must be less than or equal to the given field.
     *
     * @param  string  $field
     * @return static
     */
    public function lessThanOrEqualTo(string $field): static
    {
        return $this->addRule('lte:'.$field);
    }

    /**
     * The field under validation must be less than or equal to a maximum value.
     *
     * @param  int|float  $value
     * @return static
     */
    public function max(int|float $value): static
    {
        return $this->addRule('max:'.$value);
    }

    /**
     * The integer under validation must have a maximum number of digits.
     *
     * @param  int  $value
     * @return static
     */
    public function maxDigits(int $value): static
    {
        return $this->addRule('max_digits:'.$value);
    }

    /**
     * The field under validation must have a minimum value.
     *
     * @param  int|float  $value
     * @return static
     */
    public function min(int|float $value): static
    {
        return $this->addRule('min:'.$value);
    }

    /**
     * The integer under validation must have a minimum number of digits.
     *
     * @param  int  $value
     * @return static
     */
    public function minDigits(int $value): static
    {
        return $this->addRule('min_digits:'.$value);
    }

    /**
     * The field under validation must be a multiple of the given value.
     *
     * @param  int|float  $value
     * @return static
     */
    public function multipleOf(int|float $value): static
    {
        return $this->addRule('multiple_of:'.$value);
    }

    /**
     * The given field must match the field under validation.
     *
     * @param  string  $field
     * @return static
     */
    public function same(string $field): static
    {
        return $this->addRule('same:'.$field);
    }

    /**
     * The field under validation must match the given value.
     *
     * @param  int  $value
     * @return static
     */
    public function exactly(int $value): static
    {
        return $this->integer()->addRule('size:'.$value);
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