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

namespace Syscodes\Components\Support\Chronos\Traits;

use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use Exception;
use Syscodes\Components\Support\Chronos\Exceptions\InvalidFormatException;
use DateTimeInterface;

/**
 * Trait Creator.
 * 
 * Static creatores.
 */
trait Creator
{
    use StaticOptions;

    /**
     * Get the test of date now.
     * 
     * @var object|string
     */
    protected static $testNow;

    /**
     * Constructor. The Date class instance.
     * 
     * @param  DateTimeInterface|string|int|float|null  $time  
     * @param  DateTimeZone|string|int|null  $timezone 
     * 
     * @return void
     */
    public function __construct(
        DateTimeInterface|string|int|float|null $time = null,
        DateTimeZone|string|int|null $timezone = null
    ) {
        if (is_null($time) && static::$testNow instanceof static) {
            if (empty($timezone)) {
                $timezone = static::$testNow->getTimezone();
            }

            $time = static::$testNow->toDateTimeString();
        }
        
        $timezone = ! empty($timezone) ? $timezone : date_default_timezone_get();

        $this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
        
        if ( ! empty($time)) {
			if (is_string($time) && static::hasRelativeKeywords($time)) {
				$dateTime = new DateTime('now', $this->timezone);
				$dateTime->modify($time);

				$time = $dateTime->format('Y-m-d H:i:s');
			}
        }
        
        try {
            parent::__construct($time ?: 'now', $this->timezone ?: null);

        } catch(Exception $exception) {
            throw new InvalidFormatException($exception->getMessage(), 0, $exception);
        }    
    }

    /**
     * Create a Chronos instance from a DateTime one.
     * 
     * @param  DateTimeInterface  $date
     * 
     * @return static
     */
    public static function instance(DateTimeInterface $date): static
    {
        if ($date instanceof static) {
            return clone $date;
        }

        $instance = static::createFromFormat('U.u', $date->format('U.u'))
            ->setTimezone($date->getTimezone());

        return $instance;
    }

    /**
     * Create a carbon instance from a string.
     *
     * @param  DateTimeInterface|string|int|float|null  $time
     * @param  DateTimeZone|string|int|null  $timezone
     * 
     * @return static
     *
     * @throws InvalidFormatException
     */
    public static function rawParse(
        DateTimeInterface|string|int|float|null $time,
        DateTimeZone|string|int|null $timezone = null
    ): static {
        if ($time instanceof DateTimeInterface) {
            return static::instance($time);
        }

        try {
            return new static($time, $timezone);
        } catch (Exception $exception) {
            // @codeCoverageIgnoreStart
            try {
                $date = @static::now($timezone);
            } catch (DateMalformedStringException|InvalidFormatException) {
                $date = null;
            }
            // @codeCoverageIgnoreEnd

            return $date
                ?? throw new InvalidFormatException("Could not parse '$time': ".$exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Returns a new Time instance while parsing a datetime string.
     * 
     * @param  DateTimeInterface|string|int|float|null  $time
     * @param  DateTimeZone|string|int|null  $timezone    
     * 
     * @return static
     */
    public static function parse(
        DateTimeInterface|string|int|float|null $time,
        DateTimeZone|string|int|null $timezone = null
    ): static {
        $function = static::$parseFunction;

        if ( ! $function) {
            return static::rawParse($time, $timezone);
        }

        if (\is_string($function) && method_exists(static::class, $function)) {
            $function = [static::class, $function];
        }

        return $function(...\func_get_args());
    }

    /**
     * Returns a new Time instance with the timezone set.
     * 
     * @param  DateTimeZone|string|int|null  $timezone
     * 
     * @return static
     */
    public static function now(DateTimeZone|string|int|null $timezone = null): static
    {
        return new static(null, $timezone);
    }

    /**
     * Return a new time with the time set to midnight.
     * 
     * @param  string|null  $timezone  
     * 
     * @return static
     */
    public static function today($timezone = null): static
    {
        return static::rawParse('today', $timezone);
    }
    
    /**
     * Returns an instance set to midnight yesterday morning.
     * 
     * @param  string|null  $timezone   
     * 
     * @return static
     */
    public static function yesterday($timezone = null): static
	{
		return static::rawParse('yesterday', $timezone);
    }

    /**
     * Returns an instance set to midnight tomorrow morning.
     * 
     * @param  string|null  $timezone    
     * 
     * @return static
     */
    public static function tomorrow($timezone = null): static
	{
		return static::rawParse('tomorrow', $timezone);
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
     * @return \Syscodes\Components\Support\Chronos\Chronos
     */
    public static function createFromDate(
        ?int $year      = null, 
        ?int $month     = null, 
        ?int $day       = null, 
        $timezone      = null, 
        ?string $locale = null
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
     * @return \Syscodes\Components\Support\Chronos\Chronos
     */
    public static function createFromTime(
        ?int $hour      = null, 
        ?int $minutes   = null,
        ?int $seconds   = null,
        $timezone      = null,
        ?string $locale = null
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
     * @return \Syscodes\Components\Support\Chronos\Chronos
     */
    public static function create(
        ?int $year = null, 
        ?int $month     = null, 
        ?int $day       = null, 
        ?int $hour      = null, 
        ?int $minutes   = null,
        ?int $seconds   = null,
        $timezone      = null,
        ?string $locale = null
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
     * @return \Syscodes\Components\Support\Chronos\Chronos
     */
    public static function createFromFormat($format, $datetime, $timezone = null): DateTime
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
     * @return \Syscodes\Components\Support\Chronos\Chronos
     */
    public static function createFromTimestamp(int $timestamp, $timezone = null, ?string $locale = null)
    {
        return static::parse(date('Y-m-d H:i:s', $timestamp), $timezone, $locale);
    }

    /**
     * Creates an instance of Time that will be returned during testing
	 * when calling 'Time::now' instead of the current time.
     * 
     * @param  \Syscodes\Components\Support\Chronos\Chronos|string  $datetime  
     * @param  string|null  $timezone 
     * @param  string|null  $locale  
     * 
     * @return static
     */
    public static function setTestNow($datetime = null, $timezone = null, ?string $locale = null)
    {
        $time = '';
        
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
    public function difference($time, ?string $timezone = null)
    {
        $testTime = $this->getConvertedUTC($time, $timezone);
        $ourTime  = $this->getConvertedUTC($this);

        return $this->getDifferenceTime($ourTime, $testTime);
    }
}