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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Session\Handlers;

use SessionHandlerInterface;
use Syscodes\Components\Http\Request;
use Syscodes\Components\Support\InteractsWithTime;
use Syscodes\Components\Contracts\Cookie\QueueingFactory as Cookie;

/**
 * Session handler using array system for storage.
 */
class CookieSessionHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    /**
     * The cookie manager instance.
     * 
     * @var \Syscodes\Components\Contracts\Auth\QueueingFactory $cookie
     */
    protected $cookie;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int $minutes
     */
    protected $minutes;

    /**
     * The request instance.
     * 
     * @var \Syscodes\Components\Http\Request $request
     */
    protected $request;

    /**
     * Constructor. The DatabaseSessionHandler class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Cookie\QueueingFactory  $cookie
     * @param  int  $minutes
     * 
     * @return void
     */
    public function __construct(Cookie $cookie, int $minutes)
    {
        $this->cookie  = $cookie;
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
        $value = $this->request->cookies->get($sessionId);
        
        if ( ! is_null($decoded = json_decode($value, true)) && is_array($decoded) &&
            isset($decoded['expires']) && $this->currentTime() <= $decoded['expires']) {
                return $decoded['data'];
        }

        return '';
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
        $this->cookie->queue($sessionId, json_encode([
            'data' => $data,
            'expires' => $this->availableAt($this->minutes * 60),
        ]), $this->minutes);

        return true;
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
        $this->cookie->queue($this->cookie->erase($sessionId));

        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return int
     */
    public function gc(int $lifetime): int
    {
        return 0;
    }

    /**
     * Set the Request instance.
     * 
     * @param  \Syscodes\Components\Http\Request  $request
     * 
     * @return void
     */
    public function setRequest(Request $request): void
    {
        $this->request =  $request;
    }
}