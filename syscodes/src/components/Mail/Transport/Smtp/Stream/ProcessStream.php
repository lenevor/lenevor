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

namespace Syscodes\Components\Mail\Transport\Smtp;

use Syscodes\Components\Mail\Exceptions\TransportException;

/**
 * A stream supporting local processes.
 */
class ProcessStream extends AbstractStream
{
    /**
     * Get the command.
     * 
     * @var string $command
     */
    protected string $command;

    /**
     * Sets the command.
     * 
     * @param  string  $command
     * 
     * @return void
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * Performs any initialization needed.
     * 
     * @return void
     */
    public function initialize(): void
    {
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', '\\' === DIRECTORY_SEPARATOR ? 'a' : 'w'],
        ];
        
        $pipes = [];
        
        $this->stream = proc_open($this->command, $descriptor, $pipes);
        
        stream_set_blocking($pipes[2], false);
        
        if ($err = stream_get_contents($pipes[2])) {
            throw new TransportException('Process could not be started: '.$err);
        }
        
        $this->in  = &$pipes[0];
        $this->out = &$pipes[1];
    }
    
    /**
     * Get the streams in null.
     * 
     * @return void
     */
    public function terminate(): void
    {
        if (null !== $this->stream) {
            fclose($this->in);
            fclose($this->out);
            proc_close($this->stream);
        }
        
        parent::terminate();
    }
    
    /**
     * Get the connection of remote stream for have a description
     * of type of resource.
     * 
     * @return string
     */
    protected function getConnectionDescription(): string
    {
        return 'process '.$this->command;
    }
}