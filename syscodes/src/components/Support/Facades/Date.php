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

namespace Syscodes\Components\Support\Facades;

use Syscodes\Components\Support\Chronos;

/**
 * Initialize the Date class facade.
 *
 * @method static \Syscodes\Components\Support\Chronos\Time now(string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time parse(string $time, string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time today(string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time yesterday(string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time tomorrow(string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time createFromDate(int $year = null, int $month = null, int $day = null, string $timezone = null, string $locale = null
 * @method static \Syscodes\Components\Support\Chronos\Time createFromTime(int $hour = null, int $minutes = null, int $seconds = null, string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time create(int $year = null, int $month = null, int $day = null, int $hour = null, int $minutes = null, int $seconds = null, string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time createFromFormat(string $format, string $datetime, \DateTimeZone|string $timezone = null)
 * @method static \Syscodes\Components\Support\Chronos\Time createFromTimestamp(int $timestamp, string $timezone = null, string $locale = null)
 * @method static \Syscodes\Components\Support\Chronos\Time instance(\DateTime $datetime, string $locale = null)
 * @method static setTestNow(\Syscodes\Components\Support\Chronos\Time|string $datetime = null, string $timezone = null, string $locale = null)
 * @method static bool hasTestNow()
 * @method static void difference(string $time, string $timezone = null)
 * @method static bool equals(\Syscodes\Components\Support\Chronos\Time|\DateTime|string $time, \DateTimeZone|string string $timezone = null)
 * @method static bool isBefore(\DateTime|string $time, \DatetimeZone|string string $timezone = null)
 * @method static bool isAfter(\DateTime|string $time, \DatetimeZone|string string $timezone = null)
 * @method static bool sameAs(\Syscodes\Components\Support\Chronos\Time\DateTime|string $time, \DatetimeZone|string string $timezone = null)
 * @method static float|int getYears(bool $raw = false)
 * @method static float|int getMonths(bool $raw = false)
 * @method static float|int getWeeks(bool $raw = false)
 * @method static float|int getDays(bool $raw = false)
 * @method static float|int getHours(bool $raw = false)
 * @method static float|int getMinutes(bool $raw = false)
 * @method static float|int getSeconds(bool $raw = false)
 * @method static string humanize(string $locale = null)
 * @method static string getYear()
 * @method static string getMonth()
 * @method static string getDay()
 * @method static string getHour()
 * @method static string getMinute()
 * @method static string getSecond()
 * @method static string getDayOfWeek()
 * @method static string getDayOfYear()
 * @method static string getWeekOfMonth()
 * @method static string getWeekOfYear()
 * @method static int getAge()
 * @method static bool getDst()
 * @method static string getQuater()
 * @method static \Syscodes\Components\Support\Chronos\Time setYear(string $value)
 * @method static \Syscodes\Components\Support\Chronos\Time setMonth(string $value)
 * @method static \Syscodes\Components\Support\Chronos\Time setDay(string $value)
 * @method static \Syscodes\Components\Support\Chronos\Time setHour(string $value)
 * @method static \Syscodes\Components\Support\Chronos\Time setMinute(string $value)
 * @method static \Syscodes\Components\Support\Chronos\Time setSecond(string $value)
 * @method static int addHours(int $hours)
 * @method static int addMinutes(int $minutes)
 * @method static int addSeconds(int $seconds)
 * @method static int addYears(int $years)
 * @method static int addMonths(int $months)
 * @method static int addDays(int $days)
 * @method static int subHours(int $hours)
 * @method static int subMinutes(int $minutes)
 * @method static int subSeconds(int $seconds)
 * @method static int subYears(int $years)
 * @method static int subMonths(int $months)
 * @method static int subDays(int $days)
 * @method static \IntlCalendar getCalendar()
 * 
 * @see \Syscodes\Components\Support\time
 */
class Date extends Facade
{
    const DEFAULT_FACADE = Chronos::class;

    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'date';
    }
    
    /**
     * Resolve the facade root instance from the container.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if ( ! isset(static::$resolvedInstance[$name]) && ! isset(static::$applications, static::$applications[$name])) {
            $class = static::DEFAULT_FACADE;
            
            static::swap(new $class);
        }
        
        return parent::resolveFacadeInstance($name);
    }
}