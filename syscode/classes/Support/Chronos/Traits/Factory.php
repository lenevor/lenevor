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

use Locale;
use DateTime;
use DateTimeZone;

/**
 * Trait Factory.
 * 
 * Static factories.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
     * @return \Syscode\Support\Chronos\Time
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
     * @return \Syscode\Support\Chronos\Time
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
     * @return \Syscode\Support\Chronos\Time
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
     * @return \Syscode\Support\Chronos\Time
     */
    public static function yesterday($timezone = null, string $locale = null)
	{
		return static::parse(date('Y-m-d 00:00:00', strtotime('-1 day')), $timezone, $locale);
    }
    
    
}