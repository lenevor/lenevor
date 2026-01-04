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

namespace Syscodes\Components\Support\Chronos;

use DateTime;
use Syscodes\Components\Support\Chronos\Traits\Date;

/**
 * A simple API extension for DateTime.
 * 
 * @method now(string $timezone = null, string $locale = null)
 * @method parse(string $time, string $timezone = null, string $locale = null)
 * @method today(string $timezone = null, string $locale = null)
 * @method yesterday(string $timezone = null, string $locale = null)
 * @method tomorrow(string $timezone = null, string $locale = null)
 * @method createFromDate(int $year = null, int $month = null, int $day = null, string $timezone = null, string $locale = null
 * @method createFromTime(int $hour = null, int $minutes = null, int $seconds = null, string $timezone = null, string $locale = null)
 * @method create(int $year = null, int $month = null, int $day = null, int $hour = null, int $minutes = null, int $seconds = null, string $timezone = null, string $locale = null)
 * @method createFromFormat(string $format, string $datetime, \DateTimeZone|string $timezone = null)
 * @method createFromTimestamp(int $timestamp, string $timezone = null, string $locale = null)
 * @method instance(\DateTime $datetime, string $locale = null)
 * @method setTestNow(\Syscodes\Components\Support\Chronos\Time|string $datetime = null, string $timezone = null, string $locale = null)
 * @method bool hasTestNow()
 * @method void difference(string $time, string $timezone = null)
 * @method bool equals(\Syscodes\Components\Support\Chronos\Time|\DateTime|string $time, \DateTimeZone|string string $timezone = null)
 * @method bool isBefore(\DateTime|string $time, \DatetimeZone|string string $timezone = null)
 * @method bool isAfter(\DateTime|string $time, \DatetimeZone|string string $timezone = null)
 * @method bool sameAs(\Syscodes\Components\Support\Chronos\Time\DateTime|string $time, \DatetimeZone|string string $timezone = null)
 * @method float|int getYears(bool $raw = false)
 * @method float|int getMonths(bool $raw = false)
 * @method float|int getWeeks(bool $raw = false)
 * @method float|int getDays(bool $raw = false)
 * @method float|int getHours(bool $raw = false)
 * @method float|int getMinutes(bool $raw = false)
 * @method float|int getSeconds(bool $raw = false)
 * @method string humanize(string $locale = null)
 * @method string getYear()
 * @method string getMonth()
 * @method string getDay()
 * @method string getHour()
 * @method string getMinute()
 * @method string getSecond()
 * @method string getDayOfWeek()
 * @method string getDayOfYear()
 * @method string getWeekOfMonth()
 * @method string getWeekOfYear()
 * @method int getAge()
 * @method bool getDst()
 * @method string getQuater()
 * @method setYear(string $value)
 * @method setMonth(string $value)
 * @method setDay(string $value)
 * @method setHour(string $value)
 * @method setMinute(string $value)
 * @method setSecond(string $value)
 * @method int|object addHours(int $hours)
 * @method int|object addMinutes(int $minutes)
 * @method int|object addSeconds(int $seconds)
 * @method int|object addYears(int $years)
 * @method int|object addMonths(int $months)
 * @method int|object addDays(int $days)
 * @method int|object subHours(int $hours)
 * @method int|object subMinutes(int $minutes)
 * @method int|object subSeconds(int $seconds)
 * @method int|object subYears(int $years)
 * @method int|object subMonths(int $months)
 * @method int|object subDays(int $days)
 * @method \IntlCalendar getCalendar()
 */
class Time extends Datetime
{
    use Date;
}