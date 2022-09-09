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

namespace Syscodes\Components\Cookie\Middleware;

use Closure;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Contracts\Encryption\Encrypter as EncryptContract;
use Syscodes\Components\Http\Response;

/**
 * Allows the encrypt of a cookie string according to your the request.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class EncryptCookies
{
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
     * Indicates if cookies should be serialized.
     * 
     * @var bool $serialized
     */
    protected $serialized = false;

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
     * @param  \Closure  $next
     * 
     * @return \Syscodes\Components\Http\Response
     */
    public function handle($request, Closure $next)
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

            
        }

        return $request;
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
        }

        return $response;
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
        return in_array($name, $this->except);
    }
}