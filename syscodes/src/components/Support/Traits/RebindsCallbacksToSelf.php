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

declare(strict_types=1);

namespace Syscodes\Components\Support\Traits;

use Closure;
use ReflectionFunction;

/**
 * Trait RebindCallbackToSelf.
 */
trait RebindsCallbacksToSelf
{
    /**
     * Binds the provided callback to the class instance.
     *
     * @throws \ReflectionException
     */
    protected function bindCallbackToSelf(Closure $callback): ?Closure
    {
        $reflector = new ReflectionFunction($callback);

        // We only want to rebind anonymous functions.
        if ($reflector->isAnonymous()) {
            if ($reflector->isStatic()) {
                // Static functions are bound without $this.
                $callback = $callback->bindTo(null, static::class);
            } else {
                // Non-static functions are bound to $this.
                $callback = $callback->bindTo($this, static::class);
            }
        }

        return $callback;
    }
}