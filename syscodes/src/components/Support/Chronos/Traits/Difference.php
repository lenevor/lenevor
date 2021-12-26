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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Chronos\traits;

use Datetime;
use IntlCalendar;

/**
 * Trait Difference.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait Difference
{
    /**
     * The timestamp of the current time.
     * 
     * @var int $currentTime
     */
    protected $currentTime;

    /**
     * Difference in seconds.
     * 
     * @var int $difference
     */
    protected $difference;

    /**
     * The timestamp to compare the current time to.
     * 
     * @var int $testTime;
     */
    protected $testTime;

    // Getters

    /**
     * Get difference time.
     * 
     * @param  \DateTime  $currentTime
     * @param  \DateTime  $testTime
     * 
     * @return $this 
     */
    protected function getDifferenceTime(DateTime $currentTime, DateTime $testTime)
    {
        $this->difference  = $currentTime->getTimestamp() - $testTime->getTimestamp();
        $this->currentTime = IntlCalendar::fromDateTime($currentTime->format('Y-m-d H:i:s'));
        $this->testTime    = IntlCalendar::fromDateTime($testTime->format('Y-m-d H:i:s'))->getTime();

        return $this;
    }

    /**
     * Returns the number of years of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getYears(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 31536000;
        }

        $time = clone($this->currentTime);

        return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_YEAR);
    }

    /**
     * Returns the number of months of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getMonths(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 2629750;
        }

        $time = clone($this->currentTime);

        return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_MONTH);
    }

    /**
     * Returns the number of weeks of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getWeeks(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 604800;
        }

        $time = clone($this->currentTime);

        return (int) ($time->fieldDifference($this->testTime, IntlCalendar::FIELD_DAY_OF_YEAR) / 7);
    }

    /**
     * Returns the number of days of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getDays(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 86400;
        }

        $time = clone($this->currentTime);

        return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_DAY_OF_YEAR);
    }

    /**
     * Returns the number of hours of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getHours(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 3600;
        }

        $time = clone($this->currentTime);

        return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_HOUR_OF_DAY);
    }

    /**
     * Returns the number of minutes of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getMinutes(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 60;
        }

        $time = clone($this->currentTime);

        return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_MINUTE);
    }

    /**
     * Returns the number of seconds of difference between the two dates.
     * 
     * @param  bool  $raw
     * 
     * @return float|int
     */
    public function getSeconds(bool $raw = false)
    {
        if ($raw) {
            return $this->difference / 1;
        }

        $time = clone($this->currentTime);

        return $time->fieldDifference($this->testTime, IntlCalendar::FIELD_SECOND);
    }

    /**
     * Convert the time to human readable format.
     * 
     * @param  string|null  $locale
     * 
     * @return string
     */
    public function humanize(string $locale = null): string
    {
        $current = clone($this->currentTime);
        $years   = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_YEAR);
        $months  = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_MONTH);
        $days    = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_DAY_OF_YEAR);
        $hours   = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_HOUR_OF_DAY);
        $minutes = $current->fieldDifference($this->testTime, IntlCalendar::FIELD_MINUTE);

        $phrase = null;

        if ($years !== 0) {
            $phrase = __('time.years', [abs($years)], $locale);
            $before = $years < 0;
        } else if ($months !== 0) {
            $phrase = __('time.months', [abs($months)], $locale);
            $before = $months < 0;
        } else if ($days !== 0 && (abs($days) >= 7)) {
            $weeks  = ceil($days / 7);
            $phrase = __('time.weeks', [abs($weeks)], $locale);
            $before = $days < 0;
        } else if ($days !== 0) {
            $phrase = __('time.days', [abs($days)], $locale);
            $before = $days < 0;
        } else if ($hours !== 0) {
            $phrase = __('time.hours', [abs($hours)], $locale);
            $before = $hours < 0;
        } else if ($minutes !== 0) {
            $phrase = __('time.minutes', [abs($minutes)], $locale);
            $before = $minutes < 0;
        } else {
            return __('time.now', [], $locale);
        }
        
        return $before
            ? __('time.ago', [$phrase], $locale)
            : __('time.inFuture', [$phrase], $locale);
    }
}