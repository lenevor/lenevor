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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 * @since       0.4.1
 */

namespace Syscodes\Session\Handlers;

use Syscodes\Contracts\Cache\Store;

/**
 * Session handler using cache system for storage.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class CacheBasedSession implements SessionHandlerInterface
{
    /**
     * The cache repository instance.
     * 
     * @var \Syscodes\Contracts\Cache\Store $cache
     */
    protected $cache;

    /**
     * The number of minutes the session should be valid.
     * 
     * @var int $minutes
     */
    protected $minutes;

    /**
     * Constructor. The FileSession class instance.
     * 
     * @param  \Syscodes\Contracts\Cache\Store  $cache
     * @param  int  $minutes
     * 
     * @return void
     */
    public function __construct(store $cache, $minutes)
    {
        $this->cache   = $cache;
        $this->minutes = $minutes;
    } 
    
    /**
     * Open session name.
     * 
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }
    
    /**
     * Close session.
     * 
     * @return bool
     */
    public function close()
    {
        return true;
    }
    
    /**
     * Reads session data and acquires a lock.
     * 
     * @param  string  $sessionId
     * 
     * @return string
     */
    public function read($sessionId)
    {
        return $this->cache->get($sessionId, '');
    }
    
    /**
     * Writes (create / update) session data.
     * 
     * @param  string  $sessionId
     * @param  string  $data
     * 
     * @return bool
     */
    public function write($sessionId, $data)
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
    public function destroy($sessionId)
    {
        return $this->cache->delete($key);
    }
    
    /**
     * Deletes expired sessions.
     * 
     * @param  int  $lifetime
     * 
     * @return bool
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Get the underlying cache repository.
     * 
     * @return \Syscodes\Contracts\Cache\Store
     */
    public function getCache()
    {
        return $this->cache;
    }
}