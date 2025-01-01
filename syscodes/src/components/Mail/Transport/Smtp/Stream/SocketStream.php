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
 * A stream supporting remote sockets.
 */
final class SocketStream extends AbstractStream
{
    /**
     * Get the host in the url.
     * 
     * @var string $host
     */
    protected string $host = 'localhost';

    /**
     * The port to connection of protocol.
     * 
     * @var int $port
     */
    protected int $port = 465;

    /**
     * The time out for the send of messages.
     * 
     * @var float $timeout
     */
    protected float $timeout;

    /**
     * If indicate value boolean in true or false.
     * 
     * @var bool $tls
     */
    protected bool $tls = true;

    /**
     * Get the source ip.
     * 
     * @var string|null $sourceIp
     */
    protected ?string $sourceIp = null;

    /**
     * Get the stream context options.
     * 
     * @var array $contextOptions
     */
    protected array $contextOptions = [];

    /**
     * Get the url.
     * 
     * @var string $url
     */
    protected string $url;
    
    /**
     * Set the host.
     * 
     * @param  string  $host
     * 
     * @return static
     */
    public function setHost(string $host): static
    {
        $this->host = $host;
        
        return $this;
    }

    /**
     * Get the host.
     * 
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }
    
    /**
     * Set the port.
     * 
     * @param  int  $port
     * 
     * @return static
     */
    public function setPort(int $port): static
    {
        $this->port = $port;
        
        return $this;
    }

    /**
     * Get the port.
     * 
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }
    
    /**
     * Sets the TLS/SSL on the socket (disables STARTTLS).
     * 
     * @return static
     */
    public function disableTls(): static
    {
        $this->tls = false;
        
        return $this;
    }
    
    /**
     * If indicate that the variable is true.
     * 
     * @return bool
     */
    public function isTLS(): bool
    {
        return $this->tls;
    }
    
    /**
     * Sets the stream options.
     * 
     * @param  array  $options
     * 
     * @return static
     */
    public function setStreamOptions(array $options): static
    {
        $this->contextOptions = $options;
        
        return $this;
    }
    
    /**
     * Gets the stream options.
     * 
     * @return array
     */
    public function getStreamOptions(): array
    {
        return $this->contextOptions;
    }
    
    /**
     * Sets the source IP.
     * 
     * @param  string  $ip
     * 
     * @return static
     */
    public function setSourceIp(string $ip): static
    {
        $this->sourceIp = $ip;
        
        return $this;
    }
    
    /**
     * Returns the IP used to connect to the destination.
     *
     * @return string|null
     */
    public function getSourceIp(): ?string
    {
        return $this->sourceIp;
    }
    
    /**
     * Sets the timeout for send of messages.
     * 
     * @return static
     */
    public function setTimeout(float $timeout): static
    {
        $this->timeout = $timeout;
        
        return $this;
    }
    
    /**
     * Gets the timeout for send of messages.
     * 
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout ?? (float) ini_get('default_socket_timeout');
    }

    /**
     * Performs any initialization needed.
     * 
     * @return void
     */
    public function initialize(): void
    {
        $this->url = $this->host.':'.$this->port;
        
        if ($this->tls) {
            $this->url = 'ssl://'.$this->url;
        }
        
        $options = [];
        
        if ($this->sourceIp) {
            $options['socket']['bindto'] = $this->sourceIp.':0';
        }
        
        if ($this->contextOptions) {
            $options = array_merge($options, $this->contextOptions);
        }
        
        $options['ssl']['crypto_method'] ??= STREAM_CRYPTO_METHOD_TLS_CLIENT | 
                                             STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | 
                                             STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
                                             
        $streamContext = stream_context_create($options);
        
        $timeout = $this->getTimeout();
        
        set_error_handler(function ($type, $msg) {
            throw new TransportException(sprintf('Connection could not be established with host "%s": ', $this->url).$msg);
        });
        
        try {
            $this->stream = stream_socket_client($this->url, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $streamContext);
        } finally {
            restore_error_handler();
        }
        
        stream_set_blocking($this->stream, true);
        stream_set_timeout($this->stream, $timeout);
        
        $this->in  = &$this->stream;
        $this->out = &$this->stream;
    }
    
    /**
     * Get the streams in null.
     * 
     * @return void
     */
    public function terminate(): void
    {
        if (null !== $this->stream) {
            fclose($this->stream);
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
        return $this->url;
    }
}