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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscode\Support\Chronos\Traits;

use DateInterval;
use Syscode\Support\Chronos\Exceptions\InvalidDateTimeException;

/**
 * Trait Schedule.
 * 
 * List of methods to interact with the date and time elements.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait Schedule
{
    // Getters

    /**
     * Returns the localized Year.
     * 
     * @return string
     */
    public function getYear()
    {
        return $this->toLocalizedFormatter('Y');
    }

    /**
     * Returns the localized Month.
     * 
     * @return string
     */
    public function getMonth()
    {
        return $this->toLocalizedFormatter('M');
    }

    /**
     * Returns the localized day in the month.
     * 
     * @return string
     */
    public function getDay()
    {
        return $this->toLocalizedFormatter('d');
    }

    /**
     * Returns the localized hour (in 24-hour format).
     * 
     * @return string
     */
    public function getHour()
    {
        return $this->toLocalizedFormatter('H');
    }

    /**
     * Returns the localized minutes in the hour.
     * 
     * @return string
     */
    public function getMinute()
    {
        return $this->toLocalizedFormatter('m');
    }

    /**
     * Returns the localized Seconds.
     * 
     * @return string
     */
    public function getSecond()
    {
        return $this->toLocalizedFormatter('s');
    }

    /**
     * Returns the localized day of the week.
     * 
     * @return string
     */
    public function getDayOfWeek()
    {
        return $this->toLocalizedFormatter('c');
    }

    /**
     * Return the index of the day of the year.
     * 
     * @return string
     */
    public function getDayOfYear()
    {
        return $this->toLocalizedFormatter('D');
    }
    
    /**
     * Return the index of the week in the month.
     * 
     * @return string
     */
    public function getWeekOfMonth()
    {
        return $this->toLocalizedFormatter('W');
    }

    /**
     * Return the index of the week in the year.
     * 
     * @return string
     */
    public function getWeekOfYear()
    {
        return $this->toLocalizedFormatter('w');
    }

    /**
     * Returns the age in years from the "current" date and 'now'.
     * 
     * @return int
     */
    public function getAge()
    {
        $now  = static::now()->getTimestamp();
        $time = $this->getTimestamp();

        return max(0, date('Y', $now) - date('Y', $time));
    }

    /**
     * Allows to know if we are in daylight savings.
     * 
     * @return bool
     */
    public function getDst()
    {
        $start       = strtotime('-1 year', $this->getTimestamp());
        $end         = strtotime('+2 year', $start);
        $transitions = $this->timezone->getTransitions($start, $end);

        $dayLightSaving = false;

        foreach ($transitions as $transition)
        {
            if ($transition['time'] > $this->format('U'))
            {
               $dayLightSaving = (bool) $transition['isdst'] ?? $dayLightSaving;
            }
        }

        return $dayLightSaving;
    }

    /**
     * Returns the number of the current quarter for the year.
     * 
     * @return string
     */
    public function getQuater()
    {
        return $this->toLocalizedFormatter('Q');
    }

    // Setters

    /**
     * Sets the localized Year.
     * 
     * @param  string  $value
     * 
     * @return \Syscode\Support\Chronos\Time
     * 
     * @throws \Syscode\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setYear($value)
    {
        return $this->setValue('year', $value);
    }
    
    /**
     * Sets the localized month in the year.
     * 
     * @param  string  $value
     * 
     * @return \Syscode\Support\Chronos\Time
     * 
     * @throws \Syscode\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setMonth($value)
    {
        if (is_numeric($value) && $value < 1 || $value > 12)
        {
            throw new InvalidDateTimeException("Months must be between 1 and 12. Given: {$value}");
        }
        
        if (is_string($value) && ! is_numeric($value))
        {
            $value = date('m', strtotime("{$value} 1 2017"));
        }
        
        return $this->setValue('month', $value);
    }

    /**
     * Sets the localized day in the month.
     * 
     * @param  string  $value
     * 
     * @return \Syscode\Support\Chronos\Time
     * 
     * @throws \Syscode\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setDay($value)
    {
        if ($value < 1 || $value > 31)
        {
            throw new InvalidDateTimeException("Days must be between 1 and 31. Given: {$value}");
        }
        
        $date    = $this->getYear().'-'.$this->getMonth();
        $lastDay = date('t', strtotime($date));
        
        if ($value > $lastDay)
        {
            throw new InvalidDateTimeException("Days must be between 1 and {$lastDay}. Given: {$value}");
        }

        return $this->setValue('day', $value);
    }

    /**
     * Sets the hour of the day (24 hour cycle).
     * 
     * @param  string  $value
     * 
     * @return \Syscode\Support\Chronos\Time
     * 
     * @throws \Syscode\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setHour($value)
    {
        if ($value < 0 || $value > 23)
        {
            throw new InvalidDateTimeException("Hours must be between 0 and 23. Given: {$value}");
        }

        return $this->setValue('hour', $value);
    }

    /**
     * Sets the minute of the hour.
     * 
     * @param  string  $value
     * 
     * @return \Syscode\Support\Chronos\Time
     * 
     * @throws \Syscode\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setMinute($value)
    {
        if ($value < 0 || $value > 59)
        {
            throw new InvalidDateTimeException("Minutes must be between 0 and 59. Given: {$value}");
        }

        return $this->setValue('minute', $value);
    }

    /**
     * Sets the second of the minute.
     * 
     * @param  string  $value
     * 
     * @return \Syscode\Support\Chronos\Time
     * 
     * @throws \Syscode\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setSecond($value)
    {
        if ($value < 0 || $value > 59)
        {
            throw new InvalidDateTimeException("Seconds must be between 0 and 59. Given: {$value}");
        }

        return $this->setValue('second', $value);
    }

    // ADD|SUBTRACT

    /**
     * 
     */
    public function addSeconds(int $seconds)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$seconds} seconds"));
    }
}