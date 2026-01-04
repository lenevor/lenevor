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

namespace Syscodes\Components\Support;

use DateTime;
use DateInterval;

/**
 * Interacts with time.
 */
trait InteractsWithTime
{
    /**
     * Get the number of seconds until the given DateTime.
     * 
     * @param  \DateTime|\DateInterval|int  $delay
     * 
     * @return int
     */
    protected function secondsUntil($delay): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTime
                            ? max(0, $delay->getTimestamp() - $this->currentTime())
                            : (int) $delay;
    }

    /**
     * Get the "available at" UNIX timestamp.
     * 
     * @param  \DataTime|\DateInterval|int  $delay  
     * 
     * @return int
     */
    protected function availableAt($delay = 0): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTime
                            ? $delay->getTimestamp()
                            : $this->addRealSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     * 
     * @param  \DateTime|\DateInterval|int  $delay
     * 
     * @return \DateTime|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Chronos::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the current system time as a UNIX timestamp.
     *
     * @return int
     */
    protected function currentTime(): int
    {
        return Chronos::now()->getTimestamp();
    }

    /**
     * Add seconds to the instance using timestamp.
     * 
     * @param  int  $value
     * 
     * @return static
     */
    public function addRealSeconds($value)
    {
        return Chronos::now()->setTimestamp(Chronos::now()->getTimestamp() + $value);
    }
    
    /**
     * Given a start time, format the total run time for human readability.
     * 
     * @param  float  $startTime
     * @param  float|null  $endTime
     * 
     * @return string
     */
    protected function runTimeForHumans($startTime, $endTime = null): string
    {
        $endTime ??= microtime(true);
        
        $runTime = ($endTime - $startTime) * 1000;
        
        return $runTime > 1000
            ? Chronos::parse($runTime)->humanize()
            : number_format($runTime, 2).'ms';
    }
}