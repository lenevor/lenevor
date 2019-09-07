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

namespace Syscode\Support\Chronos;

use Locale;
use DateTime;
use DateInterval;
use IntlCalendar;
use DateTimeZone;
use IntlDateFormatter;

/**
 * A localized date/time package inspired
 * by Nesbot/Carbon.
 * 
 * A simple API extension for DateTime.
 * 
 * Requires the intl PHP extension.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class Date extends DateTime
{
    /**
     * Identifier used to get language.
     * 
     * @var string $locale
     */
    protected $locale;

    /**
	 * Used to check time string to determine if it is relative time or not.
	 *
	 * @var string $relativePattern
	 */
    protected static $relativePattern = '/this|next|last|tomorrow|yesterday|midnight|today|[+-]|first|last|ago/i';
    
    /**
     * @var \Syscode\Support\Chronos\Date $testNow
     */
    protected static $testNow;

    /**
     * Get a timezone.
     * 
     * @var string $timezone
     */
    protected $timezone;

    /**
     * Format to use when displaying datetime through __toString.
     * 
     * @var string $toStringFormat
     */
    protected $toStringFormat = 'yyyy-MM-dd HH:mm:ss';

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

        if (is_null($time) && static::$testNow instanceof static)
        {
            if (empty($timezone))
            {
                $timezone = static::$testNow->getTimezone();
            }

            $time = static::$testNow->toDateTimeString();
        }
        
        $timezone       = ! empty($timezone) ? $timezone : date_default_timezone_get();
        $this->timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
        
        if ( ! empty($time))
		{
			if (is_string($time) && static::hasRelativeKeywords($time))
			{
				$dateTime = new DateTime('now', $this->timezone);
				$dateTime->modify($time);

				$time = $dateTime->format('Y-m-d H:i:s');
			}
        }
        
        return parent::__construct($time ?: 'now', $this->timezone);
    }
    
    /**
     * Check a time string to see if it includes a relative date.
     * 
     * @param  string  $time
     * 
     * @return bool
     */
    protected static function hasRelativeKeywords(string $time)
    {
        if (preg_match('/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/', $time) !== 1)
        {
            return preg_match(static::$relativePattern, $time) > 0;
        }
        
        return false;
    }

    /**
     * Returns a new Time instance with the timezone set.
     * 
     * @param  string|null  $timezone
     * @param  string|null  $locale
     * 
     * @return \Syscode\Support\Chronos\Date
     */
    public static function now($timezone = null, string $locale = null)
    {
        return new static(null, $timezone, $locale);
    }
    
    /**
     * Returns the name of the current timezone.
     * 
     * @return string
     */
    public function getTimezoneName()
    {
        return $this->timezone->getName();
    }
}