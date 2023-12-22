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

namespace Syscodes\Components\Mail\Transport;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Syscodes\Components\Mail\Helpers\Envelope;
use Syscodes\Components\Contracts\Mail\Transport;
use Syscodes\Components\Mail\Helpers\SentMessage;
use Syscodes\Components\Mail\Mailables\RawMessage;
use Syscodes\Components\Contracts\Events\Dispatcher;

/**
 * 
 */
abstract class AbstractTransport implements Transport
{
    /**
     * The event dispatch implements instance.
     * 
     * @var Distpacher $dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * The last send of time to mail.
     * 
     * @var float $lastSent
     */
    protected float $lastSent = 0;

    /**
     * The logger implements instance.
     * 
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * Get the rate of time for send of mail.
     * 
     * @var float $rate
     */
    protected float $rate = 0;

    /**
     * Constructor. Create a new AbstractTransport class instance.
     * 
     * @param  Dispatcher|null  $dispatcher
     * @param  LoggerInterface|null  $logger
     * 
     * @return void
     */
    public function __construct(Dispatcher $dispatcher = null, LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger ?? new NullLogger;
    }
    
    /**
     * Sets the maximum number of messages to send per second (0 to disable).
     * 
     * @param  float  $rate
     * 
     * @return static
     */
    public function setMaxToSeconds(float $rate): static
    {
        if (0 >= $rate) {
            $rate = 0;
        }
        
        $this->rate = $rate;
        $this->lastSent = 0;
        
        return $this;
    }

    /**
     * Send the message of mail.
     * 
     * @param  RawMessage  $message
     * @param  Envelope|null  $envelope
     * 
     * @return SentMessage|null
     */
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {

    }
}