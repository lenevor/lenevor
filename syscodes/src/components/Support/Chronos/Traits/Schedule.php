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

namespace Syscodes\Support\Chronos\Traits;

use DateInterval;
use Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException;

/**
 * Trait Schedule.
 * 
 * List of methods to interact with the date and time elements.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
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
     * @return \Syscodes\Support\Chronos\Time
     * 
     * @throws \Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException
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
     * @return \Syscodes\Support\Chronos\Time
     * 
     * @throws \Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setMonth($value)
    {
        if (is_numeric($value) && $value < 1 || $value > 12)
        {
            throw new InvalidDateTimeException(__('time.invalidMonth', [$value]));
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
     * @return \Syscodes\Support\Chronos\Time
     * 
     * @throws \Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setDay($value)
    {
        if ($value < 1 || $value > 31)
        {
            throw new InvalidDateTimeException(__('time.invalidDay', [$value]));
        }
        
        $date    = $this->getYear().'-'.$this->getMonth();
        $lastDay = date('t', strtotime($date));
        
        if ($value > $lastDay)
        {
            throw new InvalidDateTimeException(__('time.invalidOverDay', [$lastDay, $value]));
        }

        return $this->setValue('day', $value);
    }

    /**
     * Sets the hour of the day (24 hour cycle).
     * 
     * @param  string  $value
     * 
     * @return \Syscodes\Support\Chronos\Time
     * 
     * @throws \Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setHour($value)
    {
        if ($value < 0 || $value > 23)
        {
            throw new InvalidDateTimeException(__('time.invalidHour', [$value]));
        }

        return $this->setValue('hour', $value);
    }

    /**
     * Sets the minute of the hour.
     * 
     * @param  string  $value
     * 
     * @return \Syscodes\Support\Chronos\Time
     * 
     * @throws \Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setMinute($value)
    {
        if ($value < 0 || $value > 59)
        {
            throw new InvalidDateTimeException(__('time.invalidMinutes', [$value]));
        }

        return $this->setValue('minute', $value);
    }

    /**
     * Sets the second of the minute.
     * 
     * @param  string  $value
     * 
     * @return \Syscodes\Support\Chronos\Time
     * 
     * @throws \Syscodes\Support\Chronos\Exceptions\InvalidDateTimeException
     */
    public function setSecond($value)
    {
        if ($value < 0 || $value > 59)
        {
            throw new InvalidDateTimeException(__('time.invalidSeconds', [$value]));
        }

        return $this->setValue('second', $value);
    }

    // Add|Subtract

    /**
     * Returns a new Time instance with hours added to the time.
     * 
     * @param  int  $hours
     * 
     * @return static
     */
    public function addHours(int $hours)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$hours} hours"));
    }

    /**
     * Returns a new Time instance with minutes added to the time.
     * 
     * @param  int  $minutes
     * 
     * @return static
     */
    public function addMinutes(int $minutes)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$minutes} minutes"));
    }

    /**
     * Returns a new Time instance with seconds added to the time.
     * 
     * @param  int  $seconds
     * 
     * @return static
     */
    public function addSeconds(int $seconds)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$seconds} seconds"));
    }

    /**
     * Returns a new Time instance with years added to the time.
     * 
     * @param  int  $years
     * 
     * @return static
     */
    public function addYears(int $years)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$years} years"));
    }

    /**
     * Returns a new Time instance with months added to the time.
     * 
     * @param  int  $months
     * 
     * @return static
     */
    public function addMonths(int $months)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$months} months"));
    }

    /**
     * Returns a new Time instance with days added to the time.
     * 
     * @param  int  $days
     * 
     * @return static
     */
    public function addDays(int $days)
    {
        $time = clone($this);

        return $time->add(DateInterval::createFromDateString("{$days} days"));
    }

    /**
     * Returns a new Time instance with hours subtracted from the time.
     * 
     * @param  int  $hours
     * 
     * @return static
     */
    public function subHours(int $hours)
    {
        $time = clone($this);

        return $time->sub(DateInterval::createFromDateString("{$hours} hours"));
    }

    /**
     * Returns a new Time instance with minutes subtracted from the time.
     * 
     * @param  int  $minutes
     * 
     * @return static
     */
    public function subMinutes(int $minutes)
    {
        $time = clone($this);

        return $time->sub(DateInterval::createFromDateString("{$minutes} minutes"));
    }

    /**
     * Returns a new Time instance with seconds subtracted from the time.
     * 
     * @param  int  $seconds
     * 
     * @return static
     */
    public function subSeconds(int $seconds)
    {
        $time = clone($this);

        return $time->sub(DateInterval::createFromDateString("{$seconds} seconds"));
    }

    /**
     * Returns a new Time instance with years subtracted from the time.
     * 
     * @param  int  $years
     * 
     * @return static
     */
    public function subYears(int $years)
    {
        $time = clone($this);

        return $time->sub(DateInterval::createFromDateString("{$years} years"));
    }

    /**
     * Returns a new Time instance with months subtracted from the time.
     * 
     * @param  int  $months
     * 
     * @return static
     */
    public function subMonths(int $months)
    {
        $time = clone($this);

        return $time->sub(DateInterval::createFromDateString("{$months} months"));
    }

    /**
     * Returns a new Time instance with days subtracted from the time.
     * 
     * @param  int  $days
     * 
     * @return static
     */
    public function subDays(int $days)
    {
        $time = clone($this);

        return $time->sub(DateInterval::createFromDateString("{$days} days"));
    }
}