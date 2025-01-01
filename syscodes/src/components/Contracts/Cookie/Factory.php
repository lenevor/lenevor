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
namespace Syscodes\Components\Contracts\Cookie;

/**
 * Connects with a cookie instance.
 */
interface Factory
{
    /**
     * Create a new cookie instance.
     * 
     * @param  string  $name
     * @param  string  $value
     * @param  int  $minutes
     * @param  string|null  $path
     * @param  string|null  $domain
     * @param  bool|null  $secure
     * @param  bool  $httpOnly
     * @param  bool $raw
     * @param  string|null  $sameSite
     * 
     * @return \Syscodes\Components\Http\Cookie
     */
    public function make(
        string $name,
        string $value,
        int $minutes = 0,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = null
    );

    /**
     * Create a cookie that lasts "forever" (five years).
     * 
     * @param  string  $name
     * @param  string  $value
     * @param  string|null  $path
     * @param  string|null  $domain
     * @param  bool|null  $secure
     * @param  bool  $httpOnly
     * @param  bool $raw
     * @param  string|null  $sameSite
     * 
     * @return \Syscodes\Components\Http\Cookie
     */
    public function forever(
        string $name,
        string $value,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = null
    );
    
    /**
     * Expire the given cookie.
     * 
     * @param  string  $name
     * @param  string|null  $path
     * @param  string|null  $domain
     * 
     * @return \Syscodes\Components\Http\Cookie
     */
    public function erase(string $name, ?string $path = null, ?string $domain = null);
}