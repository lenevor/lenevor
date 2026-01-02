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
namespace Syscodes\Components\Contracts\Cookie;

/**
 * Queue the cookies for send a response.
 */
interface QueueingFactory extends Factory
{
    /**
     * Determine if a cookie has been queued.
     * 
     * @param  string  $key
     * @param  string|null  $path
     * 
     * @return bool
     */
    public function hasQueued(string $key, ?string $path = null): bool;
    
    /**
     * Get a queued cookie instance.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * @param  string|null  $path
     * 
     * @return \Syscodes\Components\Http\Cookie|null
     */
    public function queued(string $key, mixed $default = null, ?string $path = null);

    /**
     * Queue a cookie to send with the next response.
     * 
     * @param  array  $parameters
     * 
     * @return void
     */
    public function queue(...$parameters): void;
    
    /**
     * Queue a cookie to expire with the next response.
     * 
     * @param  string  $name
     * @param  string|null  $path
     * @param  string|null  $domain
     * 
     * @return void
     */
    public function expire(string $name, ?string $path = null, ?string $domain = null): void;
    
    /**
     * Remove a cookie from the queue.
     * 
     * @param  string  $name
     * @param  string|null  $path
     * 
     * @return void
     */
    public function unqueue(string $name, ?string $path = null): void;
    
    /**
     * Set the default path and domain for the cookie.
     * 
     * @param  string  $path
     * @param  string  $domain
     * @param  bool  $secure
     * @param  string|null  $sameSite
     * 
     * @return static
     */
    public function setDefaultPathAndDomain(string $path, string $domain, bool $secure = false, ?string $sameSite = null): static;
    
    /**
     * Get the cookies which have been queued for the next request.
     * 
     * @return array
     */
    public function getQueuedCookies(): array;
    
    /**
     * Flush the cookies which have been queued for the next request.
     * 
     * @return static
     */
    public function flushQueuedCookies(): static;
}