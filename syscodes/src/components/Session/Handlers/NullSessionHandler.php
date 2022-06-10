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
class NullSessionHandler implements SessionHandlerInterface
{
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
        return '';
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function destroy($sessionId): bool
    {
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
}