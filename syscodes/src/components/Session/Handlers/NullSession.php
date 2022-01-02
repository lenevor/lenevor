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

/**
 * Session handler using static array for storage.
 * Intended only for use during testing.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class NullSession implements SessionHandlerInterface
{
    /**
     * Open session name.
     * 
     * @param  string  $savePath
     * @param  string  $sessionName
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
        return '';
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
        return true;
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
        return true;
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
}