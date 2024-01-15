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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Chronos\traits;

use DateTime;
use DateTimeZone;
use IntlCalendar;

/**
 * Trait Comparison.
 */
trait Utilities
{
    /**
	 * Used to check time string to determine if it is relative time or not.
	 *
	 * @var string $relativePattern
	 */
    protected static $relativePattern = '/this|next|last|tomorrow|yesterday|midnight|today|[+-]|first|last|ago/i';

    /**
     * Check a time string to see if it includes a relative date.
     * 
     * @param  string  $time
     * 
     * @return bool
     */
    protected static function hasRelativeKeywords(string $time): bool
    {
        if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $time) !== 1) {
            return preg_match(static::$relativePattern, $time) > 0;
        }
        
        return false;
    }

    // Getters

    /**
     * Returns a Time instance with the timezone converted to UTC.
     * 
     * @param  \DateTime|string  $time
     * @param  \DateTimeZone|string|null  $timezone  
     * 
     * @return \DateTime|\Syscodes\Components\Support\Chronos\Time
     */
    protected function getConvertedUTC($time, string $timezone = null)
    {
        if ($time instanceof static) {
            $time = $time->toDateTime()->setTimezone(new DateTimeZone('UTC'));
        } elseif ($time instanceof DateTime) {
            $time = $time->setTimezone(new DateTimeZone('UTC'));
        } elseif (is_string($time)) {
            $timezone = $timezone ?: $this->timezone;
            $timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone('UTC');
            $time     = new DateTime($time, $timezone);
            $time     = $time->setTimezone(new DateTimeZone('UTC'));
        }

        return $time;
    }

    /**
     * Returns the IntlCalendar object used for this object, taking 
     * into account the locale, date, etc.
     * 
     * @return \IntlCalendar
     */
    public function getCalendar()
    {
        return IntlCalendar::fromDateTime($this->toDateTimeString(), $this->locale);
    }
}