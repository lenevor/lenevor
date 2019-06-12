<?php

namespace Syscode\Debug;

use Syscode\Core\Http\Exceptions\LenevorException;

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
 * @since       0.1.1
 */
class Benchmark 
{
    /**
     * List all the timers.
     * 
     * @var array $timers
     */
    protected $timers = [];

    /**
     * Starts a timer running.
     * 
     * @param  string  $name
     * @param  float   $time
     * 
     * @return $this
     */
    public function start(string $name, float $time = null)
    {
        $this->timers[strtolower($name)] = [
            'start' => ! empty($time) ? $time : microtime(true),
            'end'   => null
        ];

        return $this;
    }

    /**
     * Stop a running timer.
     * 
     * @param  string  $name
     * 
     * @return $this
     */
    public function stop(string $name)
    {
        $name = strtolower($name);

        if (empty($this->timers[$name]))
        {
            throw new LenevorException('Cannot stop timer: invalid name given');
        }

        $this->timers[$name]['end'] = microtime(true);

        return $this;
    }

    /**
     * Returns the duration of a recorded timer.
     * 
     * @param  string  $name
     * @param  int     $decimal
     * 
     * @return null|float
     */
    public function getElapsedTime(string $name, int $decimal = 4)
    {
        $name = strtolower($name);

        if (empty($this->timers[$name]))
        {
            return null;
        }

        $timer = $this->timers[$name];

        if (empty($timer['end']))
        {
            $timer['end'] = microtime(true);
        }

        return (float) number_format($timer['end'] - $timer['start'], $decimal);
    }

    public function has(string $name)
	{
		return array_key_exists(strtolower($name), $this->timers);
	}
}
