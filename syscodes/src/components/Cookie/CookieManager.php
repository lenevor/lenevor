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
namespace Syscodes\Components\Cookie;

use Syscodes\Components\Http\Cookie;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Contracts\Cookie\QueueingFactory as CookieFactory;

/**
 * Get the cookies for have a response to a rquest of user.
 */
class CookieManager implements CookieFactory
{
    use InteractsWithTime;
    
    /**
     * The default domain (if specified).
     *
     * @var string
     */
    protected $domain;

    /**
     * The default path (if specified).
     *
     * @var string
     */
    protected $path = '/';

    /**
     * All of the cookies queued for sending.
     *
     * @var \Syscodes\Components\Http\Cookie[]
     */
    protected $queued = [];

    /**
     * The default SameSite option (defaults to lax).
     *
     * @var string
     */
    protected $sameSite = 'lax';

    /**
     * The default secure setting (defaults to null).
     *
     * @var bool|null
     */
    protected $secure;

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
    ) {
        [$path, $domain, $secure, $sameSite] = $this->getPathAndDomain($path, $domain, $secure, $sameSite);
        
        $time = ($minutes == 0) ? 0 : $this->availableAt($minutes * 60);

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

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
    ) {
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    
    /**
     * Expire the given cookie.
     * 
     * @param  string  $name
     * @param  string|null  $path
     * @param  string|null  $domain
     * 
     * @return \Syscodes\Components\Http\Cookie
     */
    public function erase(string $name, ?string $path = null, ?string $domain = null) 
    {
        return $this->make($name, '', -2628000, $path, $domain);
    }

     /**
     * Determine if a cookie has been queued.
     * 
     * @param  string  $key
     * @param  string|null  $path
     * 
     * @return bool
     */
    public function hasQueued(string $key, ?string $path = null): bool
    {
        return ! is_null($this->queued($key, null, $path));
    }
    
    /**
     * Get a queued cookie instance.
     * 
     * @param  string  $key
     * @param  mixed  $default
     * @param  string|null  $path
     * 
     * @return \Syscodes\Components\Http\Cookie|null
     */
    public function queued(string $key, mixed $default = null, ?string $path = null)
    {
        $queued = Arr::get($this->queued, $key, $default);
        
        if (null === $path) {
            return Arr::last($queued, null, $default);
        }
        
        return Arr::get($queued, $path, $default);
    }

    /**
     * Queue a cookie to send with the next response.
     * 
     * @param  array  $parameters
     * 
     * @return void
     */
    public function queue(...$parameters): void
    {
        if (isset($parameters[0]) && $parameters[0] instanceof Cookie) {
            $cookie = $parameters[0];
        } else {
            $cookie = $this->make(...array_values($parameters));
        }
        
        if ( ! isset($this->queued[$cookie->getName()])) {
            $this->queued[$cookie->getName()] = [];
        }
        
        $this->queued[$cookie->getName()][$cookie->getPath()] = $cookie;
    }
    
    /**
     * Queue a cookie to expire with the next response.
     * 
     * @param  string  $name
     * @param  string|null  $path
     * @param  string|null  $domain
     * 
     * @return void
     */
    public function expire(string $name, ?string $path = null, ?string $domain = null): void
    {
        $this->queue($this->erase($name, $path, $domain));
    }
    
    /**
     * Remove a cookie from the queue.
     * 
     * @param  string  $name
     * @param  string|null  $path
     * 
     * @return void
     */
    public function unqueue(string $name, ?string $path = null): void
    {
        if (null === $path) {
            unset($this->queued[$name]);
            
            return;
        }
        
        unset($this->queued[$name][$path]);
        
        if (empty($this->queued[$name])) {
            unset($this->queued[$name]);
        }
    }
    
    /**
     * Get the path and domain, or the default values.
     * 
     * @param  string  $path
     * @param  string  $domain
     * @param  bool|null  $secure
     * @param  string|null  $sameSite
     * 
     * @return array
     */
    protected function getPathAndDomain(string $path, string $domain, ?bool $secure = null, ?string $sameSite = null): array
    {
        return [$path ?: $this->path, $domain ?: $this->domain, is_bool($secure) ? $secure : $this->secure, $sameSite ?: $this->sameSite];
    }
    
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
    public function setDefaultPathAndDomain(string $path, string $domain, bool $secure = false, ?string $sameSite = null): static
    {
        [$this->path, $this->domain, $this->secure, $this->sameSite] = [$path, $domain, $secure, $sameSite];

        return $this;
    }
    
    /**
     * Get the cookies which have been queued for the next request.
     * 
     * @return array
     */
    public function getQueuedCookies(): array
    {
        return Arr::flatten($this->queued);
    }
    
    /**
     * Flush the cookies which have been queued for the next request.
     * 
     * @return static
     */
    public function flushQueuedCookies(): static
    {
        $this->queued = [];
        
        return $this;
    }
}