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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Routing\Exceptions;

use Exception;

/**
 * MissingThrottleLimiterException.
 */
class MissingThrottleLimiterException extends Exception
{
    /**
     * Create a new exception for invalid named throttle limiter.
     *
     * @param  string  $limiter
     * 
     * @return static
     */
    public static function forLimiter(string $limiter): static
    {
        return new static("Throttle limiter [{$limiter}] is not defined.");
    }

    /**
     * Create a new exception for an invalid throttle limiter based on a model property.
     *
     * @param  string  $limiter
     * @param  class-string  $model
     * 
     * @return static
     */
    public static function forLimiterAndUser(string $limiter, string $model): static
    {
        return new static("Throttle limiter [{$model}::{$limiter}] is not defined.");
    }
}