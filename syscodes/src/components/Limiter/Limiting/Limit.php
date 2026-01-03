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

namespace Syscodes\Components\Limiter\RateLimiting;

/**
 * Allows rate limit.
 */
class Limit
{
    /**
     * The rate limit signature key.
     *
     * @var mixed
     */
    public $key;

    /**
     * The maximum number of attempts allowed within the given number of seconds.
     *
     * @var int
     */
    public $maxAttempts;

    /**
     * The number of seconds until the rate limit is reset.
     *
     * @var int
     */
    public $decaySeconds;

    /**
     * The after callback used to determine if the limiter should be hit.
     *
     * @var ?callable
     */
    public $afterCallback = null;

    /**
     * The response generator callback.
     *
     * @var callable
     */
    public $responseCallback;

    /**
     * Constructor. Create a new limit class instance.
     *
     * @param  mixed  $key
     * @param  int  $maxAttempts
     * @param  int  $decaySeconds
     * 
     * @return void
     */
    public function __construct($key = '', int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    /**
     * Create a new rate limit.
     *
     * @param  int  $maxAttempts
     * @param  int  $decaySeconds
     * 
     * @return static
     */
    public static function perSecond($maxAttempts, $decaySeconds = 1): static
    {
        return new static('', $maxAttempts, $decaySeconds);
    }

    /**
     * Create a new rate limit.
     *
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * 
     * @return static
     */
    public static function perMinute($maxAttempts, $decayMinutes = 1): static
    {
        return new static('', $maxAttempts, 60 * $decayMinutes);
    }

    /**
     * Create a new rate limit using minutes as decay time.
     *
     * @param  int  $decayMinutes
     * @param  int  $maxAttempts
     * 
     * @return static
     */
    public static function perMinutes($decayMinutes, $maxAttempts): static
    {
        return new static('', $maxAttempts, 60 * $decayMinutes);
    }

    /**
     * Create a new rate limit using hours as decay time.
     *
     * @param  int  $maxAttempts
     * @param  int  $decayHours
     * 
     * @return static
     */
    public static function perHour($maxAttempts, $decayHours = 1): static
    {
        return new static('', $maxAttempts, 60 * 60 * $decayHours);
    }

    /**
     * Create a new rate limit using days as decay time.
     *
     * @param  int  $maxAttempts
     * @param  int  $decayDays
     * 
     * @return static
     */
    public static function perDay($maxAttempts, $decayDays = 1): static
    {
        return new static('', $maxAttempts, 60 * 60 * 24 * $decayDays);
    }

    /**
     * Create a new unlimited rate limit.
     *
     * @return static
     */
    public static function none(): static
    {
        return new Unlimited;
    }

    /**
     * Set the key of the rate limit.
     *
     * @param  mixed  $key
     * 
     * @return static
     */
    public function by($key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set the callback to determine if the limiter should be hit.
     *
     * @param  callable  $callback
     * 
     * @return static
     */
    public function after($callback): static
    {
        $this->afterCallback = $callback;

        return $this;
    }

    /**
     * Set the callback that should genethrottle the response when the limit is exceeded.
     *
     * @param  callable  $callback
     * 
     * @return static
     */
    public function response(callable $callback): static
    {
        $this->responseCallback = $callback;

        return $this;
    }

    /**
     * Get a potential fallback key for the limit.
     *
     * @return string
     */
    public function fallbackKey(): string
    {
        $prefix = $this->key ? "{$this->key}:" : '';

        return "{$prefix}attempts:{$this->maxAttempts}:decay:{$this->decaySeconds}";
    }
}