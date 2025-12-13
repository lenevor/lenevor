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
use Syscodes\Components\Finder\Finder;
use Syscodes\Components\Support\Chronos;
use Syscodes\Components\Filesystem\Filesystem;

/**
 * Session handler using file system for storage.
 */
class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * The filesystem instance.
     * 
     * @var \Syscodes\Components\Filesystem\filesystem $files
     */
    protected $files;

    /**
     * The file name.
     * 
     * @var string $path
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     * 
     * @var int $minutes
     */
    protected $minutes;

    /**
     * Constructor. The FileSessionHandler class instance.
     * 
     * @param  \Syscodes\Components\Filesystem\filesystem  $file
     * @param  string  $path
     * @param  int  $minutes
     * 
     * @return void
     */
    public function __construct(Filesystem $file, $path, $minutes)
    {
        $this->files   = $file;
        $this->path    = $path;
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
        if ($this->files->isFile($path = $this->path.DIRECTORY_SEPARATOR.$sessionId) &&
            $this->files->lastModified($path) >= Chronos::now()->subMinutes($this->minutes)->getTimestamp()) {            
            return $this->files->read($path);
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
        $this->files->put($this->path.DIRECTORY_SEPARATOR.$sessionId, $data, true);

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
        $this->files->delete($this->path.DIRECTORY_SEPARATOR.$sessionId);

        return true;
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
        $files = Finder::create()
                       ->in($this->path)
                       ->files()
                       ->ignoreDotFiles(true)
                       ->date('<= now - '.$lifetime.' seconds');

        $countSessions = 0;

        foreach ($files as $file) {
            $this->files->delete($file->getRealPath());
            
            $countSessions++;
        }

        return $countSessions;
    }
}