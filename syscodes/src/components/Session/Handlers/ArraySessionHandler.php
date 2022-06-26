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

namespace Syscodes\Components\Session\Handlers;

use SessionHandlerInterface;
use Syscodes\Components\Support\InteractsWithTime;

/**
 * Session handler using array system for storage.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ArraySessionHandler implements SessionHandlerInterface
{
    use InteractsWithTime;

    /**
     * The array of stored values.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Constructor. The ArraySessionHandler class instance.
     * 
     * @param  int  $minutes
     * 
     * @return void
     */
    public function __construct(int $minutes)
    {
        $this->minutes = $minutes;
    }    
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return string
     */
    public function read($sessionId): string
    {
        if ( ! isset($this->storage[$sessionId])) {
            return '';
        }

        $session = $this->storage[$sessionId];

        $expiration = $this->currentTime($this->minutes * 60);

        if (isset($session['time']) && $session['time'] >= $expiration) {
            return $session['data'];
        }

        return '';
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $this->storage[$sessionId] = [
            'data' => $data,
            'time' => $this->currentTime()
        ];

        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        if (isset($this->storage[$sessionId])) {
            unset($this->storage[$sessionId]);
        }

        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return int
     */
    public function gc($lifetime): int
    {
        $expiration = $this->calculateExpiration($lifetime);
        
        $countSessions = 0;
        
        foreach ($this->storage as $sessionId => $session) {
            if ($session['time'] < $expiration) {
                unset($this->storage[$sessionId]);
                
                $countSessions++;
            }
        }
        
        return $countSessions;
    }
    
    /**
     * Get the expiration time of the session.
     * 
     * @param  int  $seconds
     * 
     * @return int
     */
    protected function calculateExpiration($seconds): int
    {
        return $this->currentTime() - $seconds;
    }
}