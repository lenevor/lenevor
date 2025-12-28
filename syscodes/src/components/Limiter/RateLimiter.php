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

namespace Syscodes\Components\Limiter;

use Closure;
use Syscodes\Components\Contracts\Cache\Repository as Cache;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Support\InteractsWithTime;

use function Syscodes\Components\Support\enum_value;

/**
 * Allows the rate limiter in request and api's.
 */
class RateLimiter
{
    use InteractsWithTime;
    
    /**
     * The cache store implementation.
     * 
     * @var \Syscodes\Components\Contracts\Cache\Repository
     */
    protected $cache;
    
    /**
     * The configured limit object resolvers.
     * 
     * @var array
     */
    protected $limiters = [];

    /**
     * Constructor. Create a new rate limiter class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Cache\Repository  $cache
     * 
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
    
    /**
     * Register a named limiter configuration.
     * 
     * @param  \BackedEnum|\UnitEnum|string  $name
     * @param  \Closure  $callback
     * 
     * @return static
     */
    public function register($name, Closure $callback): static
    {
        $resolvedName = $this->resolveLimiterName($name);
        
        $this->limiters[$resolvedName] = $callback;
        
        return $this;
    }
    
    /**
     * Get the given named rate limiter.
     * 
     * @param  \BackedEnum|\UnitEnum|string  $name
     * 
     * @return \Closure|null
     */
    public function limiter($name)
    {
        $resolvedName = $this->resolveLimiterName($name);
        
        $limiter = $this->limiters[$resolvedName] ?? null;
        
        if ( ! is_callable($limiter)) {
            return;
        }
        
        return function (...$args) use ($limiter) {
            $result = $limiter(...$args);
            
            if ( ! is_array($result)) {
                return $result;
            }
            
            $duplicates = (new Collection($result))->duplicates('key');
            
            if ($duplicates->isEmpty()) {
                return $result;
            }
            
            foreach ($result as $limit) {
                if ($duplicates->contains($limit->key)) {
                    $limit->key = $limit->fallbackKey();
                }
            }
            
            return $result;
        };
    }
    
    /**
     * Attempts to execute a callback if it's not limited.
     * 
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  \Closure  $callback
     * @param  \DateTimeInterface|\DateInterval|int  $decaySeconds
     * 
     * @return mixed
     */
    public function attempt($key, $maxAttempts, Closure $callback, $decaySeconds = 60)
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }
        
        if (is_null($result = $callback())) {
            $result = true;
        }
        
        return take($result, function () use ($key, $decaySeconds) {
            $this->hit($key, $decaySeconds);
        });
    }
    
    /**
     * Determine if the given key has been "accessed" too many times.
     * 
     * @param  string  $key
     * @param  int  $maxAttempts
     * 
     * @return bool
     */
    public function tooManyAttempts($key, $maxAttempts): bool
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($this->cleanRateLimiterKey($key).':timer')) {
                return true;
            }
            
            $this->resetAttempts($key);
        }
        
        return false;
    }
    
    /**
     * Increment the counter for a given key for a given decay time.
     * 
     * @param  string  $key
     * @param  \DateTimeInterface|\DateInterval|int  $decaySeconds
     * 
     * @return int
     */
    public function hit($key, $decaySeconds = 60): int
    {
        return $this->increment($key, $decaySeconds);
    }
    
    /**
     * Increment the counter for a given key for a given decay time by a given amount.
     * 
     * @param  string  $key
     * @param  \DateTimeInterface|\DateInterval|int  $decaySeconds
     * @param  int  $amount
     * 
     * @return int
     */
    public function increment($key, $decaySeconds = 60, $amount = 1): int
    {
        $key = $this->cleanRateLimiterKey($key);
        
        $this->cache->add(
            $key.':timer', $this->availableAt($decaySeconds), $decaySeconds
        );
        
        $added = $this->cache->add($key, 0, $decaySeconds);
        
        $hits = (int) $this->cache->increment($key, $amount);
        
        if ( ! $added && $hits == 1) {
            $this->cache->put($key, 1, $decaySeconds);
        }
        
        return $hits;
    }
    
    /**
     * Decrement the counter for a given key for a given decay time by a given amount.
     * 
     * @param  string  $key
     * @param  \DateTimeInterface|\DateInterval|int  $decaySeconds
     * @param  int  $amount
     * 
     * @return int
     */
    public function decrement($key, $decaySeconds = 60, $amount = 1): int
    {
        return $this->increment($key, $decaySeconds, $amount * -1);
    }
    
    /**
     * Get the number of retries left for the given key.
     * 
     * @param  string  $key
     * @param  int  $maxAttempts
     * 
     * @return int
     */
    public function retriesLeft($key, $maxAttempts): int
    {
        return $this->remaining($key, $maxAttempts);
    }
    
    /**
     * Get the number of retries left for the given key.
     * 
     * @param  string  $key
     * @param  int  $maxAttempts
     * 
     * @return int
     */
    public function remaining($key, $maxAttempts): int
    {
        $key = $this->cleanRateLimiterKey($key);
        
        $attempts = $this->attempts($key);
        
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @param  string  $key
     * 
     * @return mixed
     */
    public function attempts($key)
    {
        $key = $this->cleanRateLimiterKey($key);

        return $this->cache->get($key, 0);
    }
    
    /**
     * Clear the hits and lockout timer for the given key.
     * 
     * @param  string  $key
     * 
     * @return void
     */
    public function clear($key): void
    {
        $key = $this->cleanRateLimiterKey($key);
        
        $this->resetAttempts($key);
        
        $this->cache->delete($key.':timer');
    }
    
    /**
     * Reset the number of attempts for the given key.
     * 
     * @param  string  $key
     * 
     * @return bool
     */
    public function resetAttempts($key): bool
    {
        $key = $this->cleanRateLimiterKey($key);
        
        return $this->cache->delete($key);
    }
    
    /**
     * Get the number of seconds until the "key" is accessible again.
     * 
     * @param  string  $key
     * 
     * @return int
     */
    public function availableIn($key): int
    {
        $key = $this->cleanRateLimiterKey($key);
        
        return max(0, $this->cache->get($key.':timer') - $this->currentTime());
    }
    
    /**
     * Clean the rate limiter key from unicode characters.
     * 
     * @param  string  $key
     * 
     * @return string
     */
    public function cleanRateLimiterKey($key): string
    {
        return preg_replace('/&([a-z])[a-z]+;/i', '$1', htmlentities($key));
    }

    /**
     * Resolve the throttle limiter name.
     *
     * @param  \BackedEnum|\UnitEnum|string  $name
     * 
     * @return string
     */
    private function resolveLimiterName($name): string
    {
        return (string) enum_value($name);
    }
}