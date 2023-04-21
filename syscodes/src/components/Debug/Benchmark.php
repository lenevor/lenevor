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

namespace Syscodes\Components\Debug;

use Syscodes\Components\Core\Http\Exceptions\LenevorException;

/**
 * Provides a simple way to measure the amount of time
 * that elapses between two points.
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
     * @param  float|null  $time
     * 
     * @return static
     */
    public function start(string $name, float $time = null): static
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
     * @return static
     */
    public function stop(string $name): static
    {
        $name = strtolower($name);

        if (empty($this->timers[$name])) {
            throw new LenevorException('Cannot stop timer: invalid name given');
        }

        $this->timers[$name]['end'] = microtime(true);

        return $this;
    }

    /**
     * Returns the duration of a recorded timer.
     * 
     * @param  string  $name
     * @param  int  $decimals
     * 
     * @return null|float
     */
    public function getElapsedTime(string $name, int $decimals = 4)
    {
        $name = strtolower($name);

        if (empty($this->timers[$name])) {
            return null;
        }

        $timer = $this->timers[$name];

        if (empty($timer['end'])) {
            $timer['end'] = microtime(true);
        }

        $operation = $timer['end'] - $timer['start'];

        return (float) number_format($operation, $decimals).$this->formatPeriod($operation);
    }

    /**
     * Returns the array of timers, with the duration pre-calculated for you.
     * 
     * @param  int  $decimals
     * 
     * @return array
     */
    public function getTimers(int $decimals = 4): array
    {
        $timers = $this->timers;

        foreach ($timers as $timer) {
            if (empty($timer['end'])) {
                $timer['end'] = microtime(true);
            }

            $operation = $timer['end'] - $timer['start'];

            $timer['duration'] = (float) number_format($operation, $decimals).$this->formatPeriod($operation);
        }

        return $timers;
    }

    /**
     * Returns the converter in words of the loading time.
     * 
     * @param  float  $operation
     * 
     * @return string
     */
    protected function formatPeriod(float $operation): string
    { 
        $duration = $operation; 
        $hours    = (int) ($duration / 60 / 60); 
        $minutes  = (int) (($duration / 60) - $hours * 60); 
        $seconds  = (int) ($duration - $hours * 60 * 60 - $minutes * 60); 
        
        if ($seconds <= 0) {
           return ' ms';
        } elseif ($seconds > 0) {
            return ' s';
        }

        return ' m';
    } 

    /**
     * Checks whether or not a timer with the specified name exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->timers);
    }
}