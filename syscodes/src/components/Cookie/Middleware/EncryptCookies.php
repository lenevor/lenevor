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

namespace Syscodes\Components\Cookie\Middleware;

use Closure;
use Syscodes\Components\Http\Cookie;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Http\Response;
use Syscodes\Components\Cookie\Concerns\CookieValue;
use Syscodes\Components\Encryption\Exceptions\DecryptException;
use Syscodes\Components\Contracts\Encryption\Encrypter as EncryptContract;

/**
 * Allows the encrypt of a cookie string according to your the request.
 */
class EncryptCookies
{
    use CookieValue;

    /**
     * The Encrypter instance.
     * 
     * @var \Syscodes\Components\Contracts\Encryption\Encrypter $encrypter
     */
    protected $encrypter;

    /**
     * The names of the cookies that should not be encrypted.
     * 
     * @var array $except
     */
    protected $except = [];
    
    /**
     * The globally ignored cookies that should not be encrypted.
     * 
     * @var array $neverEncrypt
     */
    protected static $neverEncrypt = [];

    /**
     * Indicates if cookies should be serialized.
     * 
     * @var bool $serialize
     */
    protected static $serialize = false;

    /**
     * Constructor. Create a new EncryptCookies class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Encryption\Encrypter  $encrypter
     * 
     * @return void
     */
    public function __construct(EncryptContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Disable encryption for the given cookie name(s).
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function disableFor($name): void
    {
        $this->except = array_merge($this->except, (array) $name);
    }

    /**
     * Handle an incoming request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * @param  \Closure(\Syscodes\Components\Http\Response)  $next
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, Closure $next): Response
    {
        $response = $next($this->decrypt($request));

        return $this->encrypt($response);
    }

    /**
     * Decrypt the cookies on the request.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return \Syscodes\Components\Http\Request
     */
    protected function decrypt(Request $request): Request
    {
        foreach ($request->cookies as $key => $cookie) {
            if ($this->isDisabled($key)) {
                continue;
            }

            try {
                $value = $this->decryptCookie($key, $cookie);

                $request->cookies->set($key, $this->validateValue($key, $cookie));
            } catch (DecryptException $e) {
                $request->cookies->set($key, null);
            }           
        }

        return $request;
    }

    /**
     * Decrypt the given cookie and return the value.
     * 
     * @param  string  $name
     * @param  array|string  $cookie
     * 
     * @return array|string
     */
    protected function decryptCookie($name, $cookie)
    {
        return is_array($cookie)
                        ? $this->decryptArray($cookie)
                        : $this->encrypter->decrypt($cookie, static::serialized($name));
    }

    /**
     * Decrypt an array based cookie.
     * 
     * @param  array  $cookie
     * 
     * @return array
     */
    protected function decryptArray(array $cookie): array
    {
        $decrypted = [];

        foreach ($cookie as $key => $value) {
            $decrypted[$key] = $this->encrypter->decrypt($value, static::serialized($key));
        }

        return $decrypted;
    }
    
    /**
     * Validate and remove the cookie value prefix from the value.
     * 
     * @param  string  $key
     * @param  string  $value
     * 
     * @return string|array|null
     */
    protected function validateValue(string $key, $value)
    {
        return is_array($value)
                    ? $this->validateArray($key, $value)
                    : static::validate($key, $value, $this->encrypter->getKey());
    }
    
    /**
     * Validate and remove the cookie value prefix from all values of an array.
     * 
     * @param  string  $key
     * @param  array  $value
     * 
     * @return array
     */
    protected function validateArray(string $key, array $value): array
    {
        $validated = [];
        
        foreach ($value as $index => $subValue) {
            $validated[$index] = $this->validateValue("{$key}[{$index}]", $subValue);
        }
        
        return $validated;
    }

    /**
     * Encrypt the cookies on an outgoing response.
     * 
     * @param  \Syscodes\Components\Http\Response  $response
     * 
     * @return \Syscodes\Components\Http\Response
     */
    protected function encrypt(Response $response): Response
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($this->isDisabled($cookie->getName())) {
                continue;
            }
            
            $response->headers->setCookie($this->duplicate(
                $cookie,
                $this->encrypter->encrypt(
                    static::create($cookie->getName(), $this->encrypter->getKey()).$cookie->getValue(),
                    static::serialized($cookie->getName())
                )
            ));
        }
        
        return $response;
    }
    
    /**
     * Duplicate a cookie with a new value.
     * 
     * @param  \Syscodes\Components\Http\Cookie  $cookie
     * @param  mixed  $value
     * 
     * @return \Syscodes\Components\Http\Cookie
     */
    protected function duplicate(Cookie $cookie, $value): Cookie
    {
        return $cookie->withValue($value);
    }

    /**
     * Determine whether encryption has been disabled for the given cookie.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    protected function isDisabled($name): bool
    {
        return in_array($name, array_merge($this->except, static::$neverEncrypt));
    }
    
    /**
     * Indicate that the given cookies should never be encrypted.
     * 
     * @param  array|string  $cookies
     * 
     * @return void
     */
    public static function except($cookies): void
    {
        static::$neverEncrypt = array_values(array_unique(
            array_merge(static::$neverEncrypt, Arr::wrap($cookies))
        ));
    }

    /**
     * Determine if the cookie contents should be serialized.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public static function serialized($name): bool
    {
        return static::$serialize;
    }
    
    /**
     * Flush the middleware's global state.
     * 
     * @return void
     */
    public static function flushState(): void
    {
        static::$neverEncrypt = [];
        
        static::$serialize = false;
    }
}