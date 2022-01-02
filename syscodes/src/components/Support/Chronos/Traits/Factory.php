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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Chronos\Traits;

use Locale;
use DateTime;
use DateTimeZone;

/**
 * Trait Factory.
 * 
 * Static factories.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait Factory
{
    /**
     * Constructor. The Date class instance.
     * 
     * @param  string|null  $time  
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     */
    public function __construct(string $time = null, $timezone = null, string $locale = null)
    {
        $this->locale = ! empty($locale) ? $locale : Locale::getDefault();

        if (is_null($time) && static::$testNow instanceof static) {
            if (empty($timezone)) {
                $timezone = static::$testNow->getTimezone();
            }

            $time = static::$testNow->toDateTimeString();
        }
        
        $timezone       = ! empty($timezone) ? $timezone : date_default_timezone_get();
        $this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
        
        if ( ! empty($time)) {
			if (is_string($time) && static::hasRelativeKeywords($time)) {
				$dateTime = new DateTime('now', $this->timezone);
				$dateTime->modify($time);

				$time = $dateTime->format('Y-m-d H:i:s');
			}
        }
        
        return parent::__construct($time, $this->timezone);
    }

    /**
     * Returns a new Time instance with the timezone set.
     * 
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function now($timezone = null, string $locale = null)
    {
        return new static(null, $timezone, $locale);
    }

    /**
     * Returns a new Time instance while parsing a datetime string.
     * 
     * @param  string       $time
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function parse(string $time, $timezone = null, string $locale = null)
    {
        return new static($time, $timezone, $locale);
    }

    /**
     * Return a new time with the time set to midnight.
     * 
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function today($timezone = null, string $locale = null)
    {
        return static::parse(date('Y-m-d 00:00:00'), $timezone, $locale);
    }
    
    /**
     * Returns an instance set to midnight yesterday morning.
     * 
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function yesterday($timezone = null, string $locale = null)
	{
		return static::parse(date('Y-m-d 00:00:00', strtotime('-1 day')), $timezone, $locale);
    }

    /**
     * Returns an instance set to midnight tomorrow morning.
     * 
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function tomorrow($timezone = null, string $locale = null)
	{
		return static::parse(date('Y-m-d 00:00:00', strtotime('+1 day')), $timezone, $locale);
    }

    /**
     * Returns a new instance based on the year, month and day. 
     * If any of those three are left empty, will default to the current value.
     * 
     * @param  int|null  $year  
     * @param  int|null  $month  
     * @param  int|null  $day  
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function createFromDate(
        int $year      = null, 
        int $month     = null, 
        int $day       = null, 
        $timezone      = null, 
        string $locale = null
    ) {
        return static::create($year, $month, $day, null, null, null, $timezone, $locale);
    }

    /**
     * Returns a new instance with the date set to today, and 
     * the time set to the values passed in.
     * 
     * @param  int|null  $hour  
     * @param  int|null  $minutes  
     * @param  int|null  $seconds  
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function createFromTime(
        int $hour      = null, 
        int $minutes   = null,
        int $seconds   = null,
        $timezone      = null,
        string $locale = null
    ) {
        return static::create(null, null, null, $hour, $minutes, $seconds, $timezone, $locale);
    }

    /**
     * Returns a new instance with the date time values individually set.
     * 
     * @param  int|null  $year  
     * @param  int|null  $month  
     * @param  int|null  $day  
     * @param  int|null  $hour  
     * @param  int|null  $minutes  
     * @param  int|null  $seconds  
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function create(
        int $year      = null, 
        int $month     = null, 
        int $day       = null, 
        int $hour      = null, 
        int $minutes   = null,
        int $seconds   = null,
        $timezone      = null,
        string $locale = null
    ) {
        $year    = is_null($year) ? date('Y') : $year;
        $month   = is_null($month) ? date('m') : $month;
        $day     = is_null($day) ? date('d') : $day;
        $hour    = empty($hour) ? 0 : $hour;
        $minutes = empty($minutes) ? 0 : $minutes;
        $seconds = empty($seconds) ? 0 : $seconds;

        return static::parse(date('Y-m-d H:i:s', strtotime("{$year}-{$month}-{$day} {$hour}:{$minutes}:{$seconds}")), $timezone, $locale);
    }

    /**
     * Provides a replacement for DateTime’s method of the same name.
     * This allows the timezone to be set at the same time, and returns 
     * a Time instance, instead of DateTime.
     * 
     * @param  string  $format
     * @param  string  $datetime
     * @param  \DateTimeZone|string|null  $timezone  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function createFromFormat($format, $datetime, $timezone = null)
    {
        $date = parent::createFromFormat($format, $datetime);

        return static::parse($date->format('Y-m-d H:i:s'), $timezone);
    }

    /**
     * Returns a new instance with the datetime set based on the provided UNIX timestamp.
     * 
     * @param  int  $timestamp
     * @param  string|null  $timezone  
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function createFromTimestamp(int $timestamp, $timezone = null, string $locale = null)
    {
        return static::parse(date('Y-m-d H:i:s', $timestamp), $timezone, $locale);
    }

    /**
     * Takes an instance of DateTime and returns an instance 
     * of Time with it's same values.
     * 
     * @param  \DateTime  $datetime
     * @param  string|null  $locale  
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public static function instance($datetime, string $locale = null)
    {
        $date     = $datetime->format('Y-m-d H:i:s');
        $timezone = $datetime->getTimezone();

        return static::parse($date, $timezone, $locale);
    }

    /**
     * Creates an instance of Time that will be returned during testing
	 * when calling 'Time::now' instead of the current time.
     * 
     * @param  \Syscodes\Components\Support\Chronos\Time|string  $datetime  
     * @param  string|null  $timezone 
     * @param  string|null  $locale  
     * 
     * @return static
     */
    public static function setTestNow($datetime = null, $timezone = null, string $locale = null)
    {
        if (null === $datetime) {
            static::$testNow = null;

            return;
        }

        if (is_string($datetime)) {
            $time = static::parse($datetime, $timezone, $locale);
        } elseif ($datetime instanceof DateTime && ! $datetime instanceof static) {
            $time = static::parse($datetime->format('Y-m-d H:i:s'), $timezone);
        }

        static::$testNow = $time;
    }

    /**
     * Returns whether we have a testNow instance saved.
     * 
     * @return bool
     */
    public static function hasTestNow(): bool
    {
        return ! is_null(static::$testNow);
    }

    // Difference

    /**
     * Get difference time depending on a specific period of time.
     * 
     * @param  string   $time
     * @param  string|null  $timezone  
     * 
     * @return void
     */
    public function difference($time, string $timezone = null)
    {
        $testTime = $this->getConvertedUTC($time, $timezone);
        $ourTime  = $this->getConvertedUTC($this);

        return $this->getDifferenceTime($ourTime, $testTime);
    }
}