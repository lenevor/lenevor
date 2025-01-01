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

namespace Syscodes\Components\Session\Handlers;

use SessionHandlerInterface;
use Syscodes\Components\Contracts\Cache\Store;

/**
 * Session handler using cache system for storage.
 */
class CacheSessionHandler implements SessionHandlerInterface
{
    /**
     * The cache repository instance.
     * 
     * @var \Syscodes\Components\Contracts\Cache\Store $cache
     */
    protected $cache;

    /**
     * The number of minutes the session should be valid.
     * 
     * @var int $minutes
     */
    protected $minutes;

    /**
     * Constructor. The CacheSessionHandler class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Cache\Store  $cache
     * @param  int  $minutes
     * 
     * @return void
     */
    public function __construct(Store $cache, $minutes)
    {
        $this->cache   = $cache;
        $this->minutes = $minutes;
    } 
    
    /**
     * Initialize session.
     * 
     * @param  string  $savePath
     * @param  string  $sessionName
     * 
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    
    /**
     * Close the session.
     * 
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }
    
    /**
     * Reads the session data from the session storage, and returns the results.
     * 
     * @param  string  $sessionId
     * 
     * @return string
     */
    public function read($sessionId): string
    {
        return $this->cache->get($sessionId, '');
    }
    
    /**
     * Writes the session data to the session storage.
     * 
     * @param  string  $sessionId
     * @param  string  $data
     * 
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        return $this->cache->put($sessionId, $data, $this->minutes * 60);
    }
    
    /**
     * Destroys the current session.
     * 
     * @param  string  $sessionId
     * 
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        return $this->cache->delete($sessionId);
    }
    
    /**
     * Deletes expired sessions.
     * 
     * @param  int  $lifetime
     * 
     * @return int
     */
    public function gc($lifetime): int
    {
        return 0;
    }

    /**
     * Get the underlying cache repository.
     * 
     * @return \Syscodes\Components\Contracts\Cache\Store
     */
    public function getCache()
    {
        return $this->cache;
    }
}