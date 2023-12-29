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

use Psr\Log\LoggerInterface;
use Syscodes\Components\Mail\Helpers\SentMessage;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Mail\Transport\AbstractTransport;

/**
 * Sends emails over SMTP.
 */
class SmtpTransport extends AbstractTransport
{
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
}