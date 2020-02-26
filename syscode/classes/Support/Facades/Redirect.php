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
 * @since       0.2.0
 */

namespace Syscode\Support\Facades;

/**
 * Initialize the Redirect class facade.
 *
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 * 
 * @method static \Syscode\Http\RedirectResponse back(int $status = 302, array $headers = [], bool $fallback = false)
 * @method static \Syscode\Http\RedirectResponse refresh(int $status = 302, array $headers = [])
 * @method static \Syscode\Http\RedirectResponse to(string $path, int $status = 302, array $headers = [], bool $secure = null)
 * @method static \Syscode\Http\RedirectResponse away(string $path, int $status = 302, array $headers = [])
 * @method static \Syscode\Http\RedirectResponse secure(string $path, int $status = 302, array $headers = [])
 * 
 * @see \Syscode\Routing\Redirector
 */
class Redirect extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'redirect';
    }
}