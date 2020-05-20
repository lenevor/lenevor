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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.3.0
 */

namespace Syscodes\Support\Chronos\traits;

use DateTime;
use DateTimeZone;

/**
 * Trait Comparison.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait Comparison
{
    /**
     * Determines if the datetime passed in is equal to the current instance.
     * 
     * @param  \Syscodes\Support\Chronos\Time|\DateTime|string  $time
     * @param  \DateTimeZone|string|null  $timezone
     * 
     * @return bool
     */
    public function equals($time, string $timezone = null)
    {
        $testTime = $this->getConvertedUTC($time, $timezone);
        $ourTime  = $this->toDateTime()
                         ->setTimezone(new DateTimeZone('UTC'))
                         ->format('Y-m-d H:i:s');

        return $testTime->format('Y-m-d H:i:s') === $ourTime;
    }

    /**
     * Determines if the current instance's time is before test time, 
     * after converting to UTC.
     * 
     * @param  \DateTime|string  $time
     * @param  \DatetimeZone|string|null  $timezone
     * 
     * @return bool
     */
    public function isBefore($time, string $timezone = null)
    {
        $testTime = $this->getConvertedUTC($time, $timezone)->getTimestamp();
        $ourTime  = $this->getTimestamp();

        return $testTime < $ourTime;
    }

     /**
     * Determines if the current instance's time is after test time, 
     * after converting to UTC.
     * 
     * @param  \DateTime|string  $time
     * @param  \DatetimeZone|string|null  $timezone
     * 
     * @return bool
     */
    public function isAfter($time, string $timezone = null)
    {
        $testTime = $this->getConvertedUTC($time, $timezone)->getTimestamp();
        $ourTime  = $this->getTimestamp();

        return $testTime > $ourTime;
    }

    /**
     * Ensures that the times are identical, taking timezone into account.
     * 
     * @param  \Syscodes\Support\Chronos\Time\DateTime|string  $time
     * @param  \DatetimeZone|string|null  $timezone
     * 
     * @return bool
     */
    public function sameAs($time, string $timezone = null)
    {
        $testTime = '';

        if ($time instanceof DateTime)
        {
            $testTime = $time->format('Y-m-d H:i:s');
        }
        elseif (is_string($time))
        {
            $timezone = $timezone ?: $this->timezone;
            $timezone = $timezone instanceof DateTimeZone ? $timezone : new DateTimeZone($timezone);
            $testTime = new DateTime($time, $timezone);
            $testTime = $testTime->format('Y-m-d H:i:s');
        }
        
        $ourTime = $this->toDateTimeString();
        
        return $testTime === $ourTime;
    }
}