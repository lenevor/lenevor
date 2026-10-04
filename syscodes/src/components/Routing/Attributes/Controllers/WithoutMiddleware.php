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

namespace Syscodes\Components\Routing\Attributes\Controllers;

use Attribute;

/**
 * Gets the without middleware attribute.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class WithoutMiddleware
{
    /**
     * Constructor. Create a new WithoutMiddleware instance.
     * 
     * @param  string  $middleware
     * @param  array<string>|null  $only
     * @param  array<string>|null  $except
     * @return void
     */
    public function __construct(
        public string $middleware,
        public ?array $only = null,
        public ?array $except = null,
    ) {
    }
}