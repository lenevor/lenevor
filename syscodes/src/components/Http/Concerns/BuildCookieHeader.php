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

namespace Syscodes\Components\Http\Concerns;

use Syscodes\Components\Http\Cookie;

/**
 * Trait BuildCookieHeader.
 */
trait BuildCookieHeader
{
    /**
     * Sets a new cookie in a way compatible to PHP's `setcookie(...)` function.
     * 
     * @param  string  $name  The name of the cookie which is also the key 
     * @param  string|null  $value  The value of the cookie that will be stored on the client's machine
     * @param  int  $expire  The timestamp indicating the time that the cookie will expire
     * @param  string|null  $path  The path on the server that the cookie will be valid
     * @param  string|null  $domain  The domain that the cookie will be valid
     * @param  bool  $secure  Indicates that the cookie should be sent back by the client over secure HTTPS connections only
     * @param  bool  $httpOnly  Indicates that the cookie should be accessible through the HTTP protocol only
     * @param  bool  $raw  Whether the cookie value should be sent with no url encoding
     * @param  string|null  $sameSite Indicates that the cookie should not be sent along with cross-site
     * 
     * @return bool
     */
    public static function setCookie(
        string $name,
        string $value = null,
        int $expire = 0,
        string $path = null,
        string $domain = null,
        bool $secure = false,
        bool $httpOnly = false,
        bool $raw = false,
        string $sameSite = null
    ): bool {
        return static::addHttpHeader(
                (new static)->build(
                    $name,
                    $value,
                    $expire,
                    $path,
                    $domain,
                    $secure,
                    $httpOnly,
                    $raw,
                    $sameSite
                )
            );
    }

    /**
     * Builds the HTTP header that can be used to set a cookie with the specified options.
     * 
     * @param  string  $name  The name of the cookie which is also the key 
     * @param  string|null  $value  The value of the cookie that will be stored on the client's machine
     * @param  int  $expire  The timestamp indicating the time that the cookie will expire
     * @param  string|null  $path  The path on the server that the cookie will be valid
     * @param  string|null  $domain  The domain that the cookie will be valid
     * @param  bool  $secure  Indicates that the cookie should be sent back by the client over secure HTTPS connections only
     * @param  bool  $httpOnly  Indicates that the cookie should be accessible through the HTTP protocol only
     * @param  bool  $raw  Whether the cookie value should be sent with no url encoding
     * @param  string|null  $sameSite  Indicates that the cookie should not be sent along with cross-site
     * 
     * @return string
     */
    protected function build(
        string $name,
        string $value = null,
        int $expire = 0,
        string $path = null,
        string $domain = null,
        bool $secure = false,
        bool $httpOnly = false,
        bool $raw = false,
        string $sameSite = null
    ): string {
        if ($raw) {
            $headerStr = $name;
        } else {
            $headerStr = str_replace(Cookie::SYS_RESERVED_CHARS_FROM, Cookie::SYS_RESERVED_CHARS_TO, $name);
        }
        
        $headerStr .= '=';
        
        if (is_null($value) || false === $value || '' === (string) $value) {
            $headerStr .= 'deleted; expires='.gmdate('D, d M Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $headerStr .= $raw ? $value : rawurlencode($value);

            if (0 !== $expire) {
                $headerStr .= '; expires='.gmdate('D, d M Y H:i:s T', $expire).'; Max-Age='.$this->getMaxAge();
            }
        }

        if ( ! empty($path) || 0 === $path) {
            $headerStr .= '; path='.$path;
        }

        if ( ! empty($domain) || 0 === $domain) {
            $headerStr .= '; domain='.$domain;
        }

        if (true === $secure) {
            $headerStr .= '; secure';
        }

        if (true === $httpOnly) {
            $headerStr .= '; httponly';
        }

        if (null !== $sameSite) {
            $headerStr .= '; samesite='.$sameSite;
        }

        return $headerStr;
    }
    
    /**
     * Checks whether a cookie with the specified name exists.
     * 
     * @param  string  $name  The name of the cookie to check
     * 
     * @return bool  whether  There is a cookie with the specified name
     */
    public function exists($name): bool
    {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * Returns the value from the requested cookie or, if not found,
     * the specified default value.
     * 
     * @param  string  $name  The name of the cookie to retrieve the value from
     * @param  mixed  $default  The default value to return if the requested cookie cannot be found
     * 
     * @return mixed  The value from the requested cookie or the default value
     */
    public function get($name, $default = null)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } else {
            return $default;
        }
    }
    
    /**
     * Deletes the cookie.
     * 
     * @return bool whether the cookie header has successfully been sent
     */
    public function delete(): bool
    {
        // set the copied cookie's value to an empty string which internally sets the required options for a deletion
        $this->withValue('');
        
        // save the copied "deletion" cookie
        return $this->save();
    }

    /**
     * Saves the cookie
     * 
     * @return bool whether the cookie header has successfully been sent
     */
    public function save(): bool
    {
        return self::addHttpHeader((string) $this);
    }

    /**
     * Adds the HTTP protocol with header attributes.
     * 
     * @param  string  $header
     * 
     * @return bool
     */
    private static function addHttpHeader($header): bool
    {
        if ( ! headers_sent()) {
            if ( ! empty($header)) {
                header($header, false);
                
                return true;
            }
        }
        
        return false;
    }
}