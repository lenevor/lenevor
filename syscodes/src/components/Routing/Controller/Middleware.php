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

namespace Syscodes\Components\Routing\Controller;

use Closure;
use Syscodes\Components\Support\Arr;

/**
 * Allows specify the only or except of controller methods the middleware.
 */
class Middleware
{
    /**
     * Constructor. Create a new controller middleware instance.
     *
     * @param  Closure|string|array  $middleware
     * @param  array<string>|null  $only
     * @param  array<string>|null  $except
     * @return void
     */
    public function __construct(public Closure|string|array $middleware, public ?array $only = null, public ?array $except = null)
    {
    }

    /**
     * Specify the only controller methods the middleware should apply to.
     *
     * @param  array|string  $only
     * @return $this
     */
    public function only(array|string $only): static
    {
        $this->only = Arr::wrap($only);

        return $this;
    }

    /**
     * Specify the controller methods the middleware should not apply to.
     *
     * @param  array|string  $except
     * @return $this
     */
    public function except(array|string $except): static
    {
        $this->except = Arr::wrap($except);

        return $this;
    }
}