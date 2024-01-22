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

namespace Syscodes\Components\Mail\Transport;

use Psr\Log\LoggerInterface;
use Syscodes\Components\Mail\Helpers\Envelope;
use Syscodes\Components\Mail\Helpers\SentMessage;
use Syscodes\Components\Mail\Mailables\RawMessage;

/**
 * LogTransport for sending mail using a logger of data notification.
 */
class LogTransport
{
    /**
     * The Logger instance.
     * 
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;
    
    /**
     * Construrctor. Create a new log transport class instance.
     * 
     * @param  \Psr\Log\LoggerInterface  $logger
     * 
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        $string = $message->toString();

        $this->logger->debug((string) $string);

        return new SentMessage($message, $envelope ?? Envelope::create($message));
    }
    
    /**
     * Get the logger for the LogTransport instance.
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }
    
    /**
     * Magic method.
     * 
     * Get the string representation of the transport.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return 'log';
    }
}