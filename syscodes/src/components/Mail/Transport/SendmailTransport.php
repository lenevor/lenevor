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
use InvalidArgumentException;
use Syscodes\Components\Events\Dispatcher;
use Syscodes\Components\Mail\Helpers\Envelope;
use Syscodes\Components\Mail\Helpers\SentMessage;
use Syscodes\Components\Mail\Mailables\RawMessage;
use Syscodes\Components\Mail\Transport\Smtp\ProcessStream;
use Syscodes\Components\Mail\Transport\Smtp\SmtpTransport;
use Syscodes\Components\Mail\Transport\Smtp\AbstractStream;

/**
 * SendmailTransport for sending mail through a Sendmail/Postfix.
 */
class SendmailTransport extends AbstractTransport
{
    /**
     * Get the command for sendmail.
     * 
     * @var string $command
     */
    protected string $command = '/usr/sbin/sendmail -bs';

    /**
     * Get the stream.
     * 
     * @var ProcessStream $stream
     */
    protected ProcessStream $stream;

    /**
     * Get the transport.
     * 
     * @var SmtpTransport|null $transport
     */
    protected ?SmtpTransport $transport = null;
    
    /**
     * Constructor. Create a new SendMailTransport class instance.
     * 
     * @param  string|null  $command
     * @param  Dispatcher|null  $dispatcher
     * @param  LoggerInterface|null  $logger
     * 
     * @return void
     */
    public function __construct(?string $command = null, ?Dispatcher $dispatcher = null, ?LoggerInterface $logger = null)
    {
        parent::__construct($dispatcher, $logger);
        
        if (null !== $command) {
            if ( ! str_contains($command, ' -bs') && ! str_contains($command, ' -t')) {
                throw new InvalidArgumentException(sprintf('Unsupported sendmail command flags "%s"; must be one of "-bs" or "-t" but can include additional flags', $command));
            }
            
            $this->command = $command;
        }
        
        $this->stream = new AbstractStream();
        
        if (str_contains($this->command, ' -bs')) {
            $this->stream->setCommand($this->command);
            $this->transport = new SmtpTransport($this->stream, $dispatcher, $logger);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        if ($this->transport) {
            return $this->transport->send($message, $envelope);
        }
        
        return parent::send($message, $envelope);
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
        $this->getLogger()->debug(sprintf('Email transport "%s" starting', __CLASS__));
        
        $command = $this->command;
        
        if ($recipients = $message->getEnvelope()->getRecipients()) {
            $command = str_replace(' -t', '', $command);
        }
        
        if ( ! str_contains($command, ' -f')) {
            $command .= ' -f'.escapeshellarg($message->getEnvelope()->getSender()->getAddress());
        }
        
        $chunks = AbstractStream::replace("\r\n", "\n", $message->toIterable());
        
        if ( ! str_contains($command, ' -i') && ! str_contains($command, ' -oi')) {
            $chunks = AbstractStream::replace("\n.", "\n..", $chunks);
        }
        
        foreach ($recipients as $recipient) {
            $command .= ' '.escapeshellarg($recipient->getAddress());
        }
        
        $this->stream->setCommand($command);
        $this->stream->initialize();
        
        foreach ($chunks as $chunk) {
            $this->stream->write($chunk);
        }
        
        $this->stream->flush();
        $this->stream->terminate();
        $this->getLogger()->debug(sprintf('Email transport "%s" stopped', __CLASS__));
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
        if ($this->transport) {
            return (string) $this->transport;
        }
        
        return 'smtp://sendmail';
    }
}