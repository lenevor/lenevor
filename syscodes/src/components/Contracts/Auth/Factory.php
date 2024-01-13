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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Contracts\Auth;

/**
 * Determine a guard by name.
 */
interface Factory
{
    /**
     * Get a guard instance by name.
     * 
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Contracts\Auth\Guard|\Syscodes\Components\Contracts\Auth\StateGuard
     */
    public function guard(string $name = null);
    
    /**
     * Set the default guard the factory should serve.
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function shouldUse(string $name): void;
}