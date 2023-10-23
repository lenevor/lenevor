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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Chronos\Traits;

use DateTime;
use IntlCalendar;
use IntlDateFormatter;

/**
 * A localized date/time package inspired
 * by Nesbot/Carbon.
 * 
 * A simple API extension for DateTime.
 * 
 * Requires the intl PHP extension.
 * 
 * @method now($timezone = null, string $locale = null)            Returns a new Time instance with the timezone
 * @method today($timezone = null, string $locale = null)          Return a new time with the time set to midnight.
 * @method yesterday($timezone = null, string $locale = null)      Returns an instance set to midnight yesterday morning. 
 * @method tomorrow($timezone = null, string $locale = null)       Returns an instance set to midnight tomorrow morning.
 */
trait Date
{
    use Factory;
    use Schedule;
    use Utilities;
    use Comparison;
    use Difference;

    /**
     * Identifier used to get language.
     * 
     * @var string $locale
     */
    protected $locale;

    /**
     * Get a timezone.
     * 
     * @var string|object $timezone
     */
    protected $timezone;

    /**
     * Format to use when displaying datetime through __toString.
     * 
     * @var string $toStringFormat
     */
    protected $toStringFormat = 'yyyy-MM-dd HH:mm:ss';

    // Getters
    
    /**
     * Returns the name of the current timezone.
     * 
     * @return string
     */
    public function getTimezoneName(): string
    {
        return $this->timezone->getName();
    }

    /**
     * Returns boolean whether object is in UTC.
     * 
     * @return bool
     */
    public function getUtc(): bool
    {
        return $this->getOffset() === 0;
    }

    /**
     * Get the locale of system.
     * 
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Returns boolean whether the passed timezone is the same as
	 * the local timezone.
     * 
     * @return bool
     */
    public function getLocalized(): bool
    {
        $local = date_default_timezone_get();

        return $local === $this->getTimezoneName();
    }

    // Setters

    /**
     * Returns a new instance with the timezone.
     * 
     * @param  \DateTimeZone  $timezone
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public function setTimezone($timezone): DateTime
    {
        return static::parse($this->toDateTimeString(), $timezone, $this->locale);
    }

    /**
     * Returns a new instance with the date set to the new timestamp.
     * 
     * @param  int  $timestamp
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    public function setTimestamp($timestamp): DateTime
    {
        $time = date('Y-m-d H:i:s', $timestamp);

        return static::parse($time, $this->timezone, $this->locale);
    }

    /**
     * Helper method to capture the data of reference of the 'setX' methods.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return \Syscodes\Components\Support\Chronos\Time
     */
    protected function setValue(string $name, $value)
    {
        list($year, $month, $day, $hour, $minute, $second) = explode('-', $this->format('Y-n-j-G-i-s'));
        $$name                                             = $value;

        return static::create(
            $year, $month, $day, $hour, $minute, $second, $this->getTimezoneName(), $this->locale
        );
    }

    // Formatters

    /**
     * Converts the current instance to a mutable DateTime object.
     * 
     * @return \DateTime
     */
    public function toDateTime()
    {
        $datetime = (new DateTime('', $this->getTimezone()))->setTimestamp(parent::getTimestamp());
        
        return $datetime;
    }

    /**
     * Returns the localized value of the date in the format 'Y-m-d H:i:s'.
     * 
     * @return mixed
     */
    public function toDateTimeString()
    {
        return $this->toLocalizedFormatter('yyyy-MM-dd HH:mm:ss');
    }

    /**
     * Returns a localized version of the date in Y-m-d format.
     * 
     * @return mixed
     */
    public function toFormattedDateString()
    {
        return $this->toLocalizedFormatter('MMM d, yyyy');
    }

    /**
     * Returns a localized version of the date in Y-m-d format.
     * 
     * @return string
     */
    public function toDateString()
    {
        return $this->toLocalizedFormatter('yyyy-MM-dd');
    }

     /**
     * Returns a localized version of the time in nicer date format.
     * 
     * @return string
     */
    public function toTimeString()
    {
        return $this->toLocalizedFormatter('HH:mm:ss');
    }

    /**
     * Returns the localized value of this instance in a format specific by the user.
     * 
     * @param  string|null  $format
     * 
     * @return string|bool
     */
    public function toLocalizedFormatter(?string $format = null)
    {
        $format = $format ?? $this->toStringFormat;

        return IntlDateFormatter::formatObject($this->toDateTime(), $format, $this->locale);
    }

    // Difference

    /**
     * Returns a text string that is easily readable that describes a 
     * date and time that has elapsed in a period of time specified 
     * by the user or system, like:
     * 
     * - 10 days ago
     * - in 2 days
     * - 9 hours ago
     * 
     * @return mixed
     */
    public function humanize()
    {
        $now     = IntlCalendar::fromDateTime(static::now($this->timezone)->toDateTimeString(), $this->locale);
        $time    = $this->getCalendar()->getTime();
        $years   = $now->fieldDifference($time, IntlCalendar::FIELD_YEAR);
        $months  = $now->fieldDifference($time, IntlCalendar::FIELD_MONTH);
        $days    = $now->fieldDifference($time, IntlCalendar::FIELD_DAY_OF_YEAR);
        $hours   = $now->fieldDifference($time, IntlCalendar::FIELD_HOUR_OF_DAY);
        $minutes = $now->fieldDifference($time, IntlCalendar::FIELD_MINUTE);

        $phrase = null;
        
        if ($years !== 0) {
            $phrase = __('time.years', [abs($years)]);
            $before = $years < 0;
        } elseif ($months !== 0) {
            $phrase = __('time.months', [abs($months)]);
            $before = $months < 0;
        } elseif ($days !== 0 && (abs($days) >= 7)) {
            $weeks  = ceil($days / 7);
            $phrase = __('time.weeks', [abs($weeks)]);
            $before = $days < 0;
        } elseif ($days !== 0) {
            $before = $days < 0;
            $phrase = __('time.days', [abs($days)]);
            
            // Yesterday/Tomorrow special cases
            if (abs($days) === 1) {
                return $before ? __('time.yesterday') : __('time.tomorrow');
            } else {
                $phrase = __('time.days', [abs($days) + 1]);
            }
        } elseif ($hours !== 0) {
            // Display the actual time instead of a regular phrase.
            return $this->format('g:i a');
        } elseif ($minutes !== 0) {
            $phrase = __('time.minutes', [abs($minutes)]);
            $before = $minutes < 0;
        } else {
            return __('time.now');
        }
        
        return $before 
            ? __('time.ago', [$phrase]) 
            : __('time.inFuture', [$phrase]);
    }

    // Magic Methods

    /**
     * Allow for property-type access to any getX method.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get'.ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
    
    /**
     * Magic method.
     * 
     * Outputs a short format version of the datetime.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return IntlDateFormatter::formatObject($this->toDateTime(), $this->toStringFormat, $this->locale);
    }
}