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

namespace Syscodes\components\Mail\Transport\Smtp;

use LogicException;
use BadMethodCallException;
use Psr\Log\LoggerInterface;
use Syscodes\Components\Mail\Helpers\SentMessage;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Mail\Transport\AbstractTransport;
use Syscodes\Components\Mail\Exceptions\TransportException;

/**
 * Sends emails over SMTP.
 */
class SmtpTransport extends AbstractTransport
{
    /**
     * Get the domain.
     * 
     * @var string $domain
     */
    protected string $domain = '[127.0.0.1]';
    
    /**
     * Indicates the initialize of variable as boolean.
     * 
     * @var bool $started
     */
    protected bool $started = false;

    /**
     * The abstract stream instance.
     * 
     * @var AbstractStream $stream
     */
    protected AbstractStream $stream;

    /**
     * Constructor. Create a new SmtpTransport class instance.
     * 
     * @param  AbstractStream|null  $stream
     * @param  Dispatcher|null  $dispatcher
     * @param  LoggerInterface|null  $logger
     * 
     * @return void
     */
    public function __construct(AbstractStream $stream = null, Dispatcher $dispatcher = null, LoggerInterface $logger = null)
    {
        parent::__construct($dispatcher, $logger);
        
        $this->stream = $stream ?? new SocketStream();
    }

    /**
     * Get the stream connection for send of messages.
     * 
     * @return AbstractStream
     */
    public function getStream(): AbstractStream
    {
        return $this->stream;
    }
    
    /**
     * Sets the name of the local domain that will be used in HELO.
     * 
     * @param  string  $domain
     * 
     * @return static
     */
    public function setLocalDomain(string $domain): static
    {
        if ('' !== $domain && '[' !== $domain[0]) {
            if (filter_var($domain, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
                $domain = '['.$domain.']';
            } elseif (filter_var($domain, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
                $domain = '[IPv6:'.$domain.']';
            }
        }
        
        $this->domain = $domain;
        
        return $this;
    }
    
    /**
     * Gets the name of the domain that will be used in HELO.
     * 
     * @return string
     */
    public function getLocalDomain(): string
    {
        return $this->domain;
    }

    /**
     * Do send to mail.
     * 
     * @param  SentMessage  $message
     * 
     * @return void
     */
    protected function doSend(SentMessage $message): void
    {
        
    }
    
    /**
     * Runs a command against the stream, expecting the given response codes.
     * 
     * @param int[] $codes
     * 
     * @throws TransportException
     */
    public function executeCommand(string $command, array $codes): string
    {
        $this->stream->write($command);        
        $response = $this->getFullResponse();        
        $this->assertResponseCode($response, $codes);
        
        return $response;
    }
    
    /**
     * Manually disconnect from the SMTP server. In most cases this is not 
     * necessary since the disconnect happens automatically on termination.
     * 
     * @return void
     */
    public function stop(): void
    {
        if ( ! $this->started) {
            return;
        }
        
        $this->getLogger()->debug(sprintf('Email transport "%s" stopping', __CLASS__));
        
        try {
            $this->executeCommand("QUIT\r\n", [221]);
        } catch (TransportException) {
            //
        } finally {
            $this->stream->terminate();
            $this->started = false;
            $this->getLogger()->debug(sprintf('Email transport "%s" stopped', __CLASS__));
        }
    }
    
    /**
     * Get assert of response code.
     * 
     * @param  string  $response
     * @param  array  $codes
     * 
     * @return void
     * 
     * @throws TransportException
     */
    private function assertResponseCode(string $response, array $codes): void
    {
        if ( ! $codes) {
            throw new LogicException('You must set the expected response code');
        }
        
        [$code] = sscanf($response, '%3d');
        $valid  = in_array($code, $codes);
        
        if ( ! $valid || !$response) {
            $codeStr     = $code ? sprintf('code "%s"', $code) : 'empty code';
            $responseStr = $response ? sprintf(', with message "%s"', trim($response)) : '';
            
            throw new TransportException(sprintf('Expected response code "%s" but got ', implode('/', $codes)).$codeStr.$responseStr.'.', $code ?: 0);
        }
    }
    
    /**
     * Get the full response.
     * 
     * @return string
     */
    private function getFullResponse(): string
    {
        $response = '';
        
        do {
            $line      = $this->stream->readLine();
            $response .= $line;
        } while ($line && isset($line[3]) && ' ' !== $line[3]);
        
        return $response;
    }

    /**
     * Magic method.
     * 
     * Returns the protocol of connection.
     * 
     * @return string
     */
    public function __toString(): string
    {
        if ($this->stream instanceof SocketStream) {
            $name = sprintf('smtp%s://%s', ($tls = $this->stream->isTLS()) ? 's' : '', $this->stream->getHost());
            $port = $this->stream->getPort();
            
            if ( ! (25 === $port || ($tls && 465 === $port))) {
                $name .= ':'.$port;
            }
            
            return $name;
        }

        return 'smtp://sendmail';
    }
    
    /**
     * Magic method.
     * 
     * Returns an array with the names of all the variables 
     * of the object to be serialized.
     * 
     * @return array
     */
    public function __sleep(): array
    {
        throw new BadMethodCallException('Cannot serialize '.__CLASS__);
    }
    
    /**
     * Magic method.
     * 
     * Reestablish connections that may have been lost 
     * during serialization.
     * 
     * @return void
     */
    public function __wakeup(): void
    {
        throw new BadMethodCallException('Cannot unserialize '.__CLASS__);
    }
    
    /**
     * Magic method.
     * 
     * Called as soon as are not other references to a given object,
     * or in any other finalization circumstance.
     * 
     * @return void
     */
    public function __destruct()
    {
        $this->stop();
    }
}