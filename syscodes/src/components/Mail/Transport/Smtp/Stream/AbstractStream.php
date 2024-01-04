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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Transport\Smtp;

use Generator;
use Syscodes\Components\Mail\Exceptions\TransportException;

/**
 * Allows the stream supporting remote sockets and local processes.
 */
abstract class AbstractStream
{
    /**
     * Get the debug.
     * 
     * @var string $debug
     */
    protected string $debug = '';

    /**
     * In the remote socket.
     * 
     * @var resource|null $in
     */
    protected $in;

    /**
     * Out the remote socket.
     * 
     * @var resource|null $out
     */
    protected $out;

    /**
     * Get the stream remote sockets.
     * 
     * @var resource|null $stream
     */
    protected $stream;

    /**
     * Performs any initialization needed.
     * 
     * @return void
     */
    abstract public function initialize(): void;
    
    /**
     * Get the connection of remote stream for have a description
     * of type of resource.
     * 
     * @return string
     */
    abstract protected function getConnectionDescription(): string;

    /**
     * Get the write of content for send to socket.
     * 
     * @param  string  $bytes
     * @param  bool  $debug
     * 
     * @return void
     */
    public function write(string $bytes, bool $debug = true): void
    {
        if ($debug) {
            foreach (explode("\n", trim($bytes)) as $line) {
                $this->debug .= sprintf("> %s\n", $line);
            }
        }
        
        $bytesToWrite = strlen($bytes);
        $totalBytesWritten = 0;
        
        while ($totalBytesWritten < $bytesToWrite) {
            $bytesWritten = @fwrite($this->in, substr($bytes, $totalBytesWritten));
            
            if (false === $bytesWritten || 0 === $bytesWritten) {
                throw new TransportException('Unable to write bytes on the wire');
            }
            
            $totalBytesWritten += $bytesWritten;
        }
    }
    
    /**
     * Flushes the contents of the stream.
     * 
     * @return void
     */
    public function flush(): void
    {
        fflush($this->in);
    }

    /**
     * Get the read line for the console.
     * 
     * @return string
     */
    public function readLine(): string
    {
        if (feof($this->out)) {
            return '';
        }
        
        $line = fgets($this->out);
        
        if ('' === $line || false === $line) {
            $meta = stream_get_meta_data($this->out);
            
            if ($meta['timed_out']) {
                throw new TransportException(sprintf('Connection to "%s" timed out', $this->getConnectionDescription()));
            }
            
            if ($meta['eof']) {
                throw new TransportException(sprintf('Connection to "%s" has been closed unexpectedly', $this->getConnectionDescription()));
            }
        }
        
        $this->debug .= sprintf('< %s', $line);
        
        return $line;
    }
    
    /**
     * Get the debug.
     * 
     * @return string
     */
    public function getDebug(): string
    {
        $debug = $this->debug;
        
        $this->debug = '';
        
        return $debug;
    }

    /**
     * Replaces a string in chunks.
     * 
     * @param  string  $from
     * @param  string  $to
     * @param  iterable  $chunks
     * 
     * @return Generator
     */
    public static function replace(string $from, string $to, iterable $chunks): Generator
    {
        if ('' === $from) {
            yield from $chunks;
            
            return;
        }
        
        $carry   = '';
        $fromLen = strlen($from);
        
        foreach ($chunks as $chunk) {
            if ('' === $chunk = $carry.$chunk) {
                continue;
            }
            
            if (str_contains($chunk, $from)) {
                $chunk = explode($from, $chunk);
                $carry = array_pop($chunk);
                
                yield implode($to, $chunk).$to;
            } else {
                $carry = $chunk;
            }
            
            if (strlen($carry) > $fromLen) {
                yield substr($carry, 0, -$fromLen);
                
                $carry = substr($carry, -$fromLen);
            }
        }
        
        if ('' !== $carry) {
            yield $carry;
        }
    }
    
    /**
     * Get the streams in null.
     * 
     * @return void
     */
    public function terminate(): void
    {
        $this->stream = $this->out = $this->in = null;
    }
}