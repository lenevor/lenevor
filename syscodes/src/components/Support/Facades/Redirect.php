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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the Redirect class facade.
 *
 * @author Alexander Campo <jalexcam@gmail.com>
 * 
 * @method static \Syscodes\Components\Http\RedirectResponse back(int $status = 302, array $headers = [], bool $fallback = false)
 * @method static \Syscodes\Components\Http\RedirectResponse refresh(int $status = 302, array $headers = [])
 * @method static \Syscodes\Components\Http\RedirectResponse to(string $path, int $status = 302, array $headers = [], bool $secure = null)
 * @method static \Syscodes\Components\Http\RedirectResponse away(string $path, int $status = 302, array $headers = [])
 * @method static \Syscodes\Components\Http\RedirectResponse secure(string $path, int $status = 302, array $headers = [])
 * 
 * @see \Syscodes\Components\Routing\Redirector
 */
class Redirect extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'redirect';
    }
}