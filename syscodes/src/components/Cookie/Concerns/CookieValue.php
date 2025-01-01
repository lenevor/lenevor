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

namespace Syscodes\Components\Cookie\Concerns;

use Syscodes\Components\Support\Str;

/**
 * Trait Cookie value.
 */
trait CookieValue
{
    /**
     * Validate a cookie value contains a valid prefix.
     * 
     * @param  string  $cookieName
     * @param  string  $cookieValue
     * @param  string  $key
     * 
     * @return string|null
     */
    public static function validate(string $cookieName, string $cookieValue, string $key): ?string
    {
        $hasValid = Str::startsWith($cookieValue, static::create($cookieName, $key));

        return $hasValid ? static::remove($cookieValue) : null;
    }

    /**
     * Create a new cookie value prefix for the given cookie name.
     * 
     * @param  string  $cookieName
     * @param  string  $key
     * 
     * @return string
     */
    public static function create(string $cookieName, string $key): string
    {
        return hash_hmac('sha1', $cookieName.'v2', $key).'|';
    }

    /**
     * Remove the cookie value prefix.
     * 
     * @param  string  $cookieValue
     * 
     * @return string
     */
    public static function remove(string $cookieValue): string
    {
        return substr($cookieValue, 41);
    }
}