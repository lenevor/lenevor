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

use Syscodes\Components\Support\Collection;
use Syscodes\Components\Mail\Helpers\Envelope;
use Syscodes\Components\Contracts\Mail\Transport;
use Syscodes\Components\Mail\Mailables\RawMessage;
use Syscodes\Components\Mail\Helpers\BaseSentMessage;

/**
 * ArrayTransport for sending mail through a array data.
 */
class ArrayTransport implements Transport
{
    /**
     * The collection of messages.
     * 
     * @var \Syscodes\Components\Support\Collection $messages
     */
    protected $messages;
    
    /**
     * Constructor. Create a new array transport class instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->messages = new Collection;
    }
    
    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, Envelope $envelope = null): ?BaseSentMessage
    {
        return $this->messages[] = new BaseSentMessage($message, $envelope ?? Envelope::create($message));
    }
    
    /**
     * Retrieve the collection of messages.
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    public function messages()
    {
        return $this->messages;
    }
    
    /**
     * Clear all of the messages from the local collection.
     * 
     * @return \Syscodes\Components\Support\Collection
     */
    public function flush()
    {
        return $this->messages = new Collection;
    }
    
    /**
     * Method magic.
     * 
     * Get the string representation of the transport.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return 'array';
    }
}