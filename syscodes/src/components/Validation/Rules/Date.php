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

use DateTimeInterface;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\Traits\Conditionable;
use Syscodes\Components\Support\Traits\Macroable;
use Stringable;

/**
 * Gets the attribute must be date.
 */
class Date implements Stringable
{
    use Conditionable, Macroable;

    /**
     * The constraints for the date rule.
     */
    protected array $constraints = [];

    /**
     * The format of the date.
     */
    protected ?string $format = null;

    /**
     * Ensure the date has the given format.
     * 
     * @param  string  $format
     * @return static
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Ensure the date is before today.
     * 
     * @return static
     */
    public function beforeToday(): static
    {
        return $this->before('today');
    }

    /**
     * Ensure the date is after today.
     * 
     * @return static
     */
    public function afterToday(): static
    {
        return $this->after('today');
    }

    /**
     * Ensure the date is before or equal to today.
     * 
     * @return static
     */
    public function todayOrBefore(): static
    {
        return $this->beforeOrEqual('today');
    }

    /**
     * Ensure the date is after or equal to today.
     * 
     * @return static
     */
    public function todayOrAfter(): static
    {
        return $this->afterOrEqual('today');
    }

    /**
     * Ensure the date is in the past.
     * 
     * @return static
     */
    public function past(): static
    {
        return $this->before('now');
    }

    /**
     * Ensure the date is in the future.
     * 
     * @return static
     */
    public function future(): static
    {
        return $this->after('now');
    }

    /**
     * Ensure the date is now or in the past.
     * 
     * @return static
     */
    public function nowOrPast(): static
    {
        return $this->beforeOrEqual('now');
    }

    /**
     * Ensure the date is now or in the future.
     * 
     * @return static
     */
    public function nowOrFuture(): static
    {
        return $this->afterOrEqual('now');
    }

    /**
     * Ensure the date is before the given date or date field.
     * 
     * @param  DateTimeInterface|string  $date
     * @return static
     */
    public function before(DateTimeInterface|string $date): static
    {
        return $this->addRule('before:'.$this->formatDate($date));
    }

    /**
     * Ensure the date is after the given date or date field.
     * 
     * @param  DateTimeInterface|string  $date
     * @return static
     */
    public function after(DateTimeInterface|string $date): static
    {
        return $this->addRule('after:'.$this->formatDate($date));
    }

    /**
     * Ensure the date is on or before the specified date or date field.
     * 
     * @param  DateTimeInterface|string  $date
     * @return static
     */
    public function beforeOrEqual(DateTimeInterface|string $date): static
    {
        return $this->addRule('before_or_equal:'.$this->formatDate($date));
    }

    /**
     * Ensure the date is on or after the given date or date field.
     * 
     * @param  DateTimeInterface|string  $date
     * @return static
     */
    public function afterOrEqual(DateTimeInterface|string $date): static
    {
        return $this->addRule('after_or_equal:'.$this->formatDate($date));
    }

    /**
     * Ensure the date is between two dates or date fields.
     * 
     * @param  DateTimeInterface|string  $from
     * @param  DateTimeInterface|string  $to
     * @return static
     */
    public function between(DateTimeInterface|string $from, DateTimeInterface|string $to): static
    {
        return $this->after($from)->before($to);
    }

    /**
     * Ensure the date is between or equal to two dates or date fields.
     * 
     * @param  DateTimeInterface|string  $from
     * @param  DateTimeInterface|string  $to
     * @return static
     */
    public function betweenOrEqual(DateTimeInterface|string $from, DateTimeInterface|string $to): static
    {
        return $this->afterOrEqual($from)->beforeOrEqual($to);
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

    /**
     * Format the date for the validation rule.
     * 
     * @param  DateTimeInterface|string  $date
     * @return string
     */
    protected function formatDate(DateTimeInterface|string $date): string
    {
        return $date instanceof DateTimeInterface
            ? $date->format($this->format ?? 'Y-m-d')
            : $date;
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
        return implode('|', [
            $this->format === null ? 'date' : 'date_format:'.$this->format,
            ...$this->constraints,
        ]);
    }
}