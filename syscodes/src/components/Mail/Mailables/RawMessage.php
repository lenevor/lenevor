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

namespace Syscodes\Components\Mail\Mailables;

use Generator;
use LogicException;

/**
 * Sending of raw message.
 */
class RawMessage
{
    /**
     * A query, is generator closed?.
     * 
     * @var bool $isGeneratorclosed
     */
    protected bool $isGeneratorClosed;

    /**
     * Get the message.
     * 
     * @var iterable|string|null $message
     */
    protected iterable|string|null $message = null;

    /**
     * Constructor. Create a new RawMessage class instance.
     * 
     * @param  iterable|string  $message
     * 
     * @return void
     */
    public function __construct(iterable|string $message)
    {
        $this->message = $message;
    }

    /**
     * Get a message of a string .
     * 
     * @return string
     */
    public function toString(): string
    {
        if (is_string($this->message)) {
            return $this->message;
        }
        
        $message = '';
        
        foreach ($this->message as $chunk) {
            $message .= $chunk;
        }
        
        return $this->message = $message;
    }

    /**
     * Get a message to iterate.
     * 
     * @return iterable
     */
    public function toIterable(): iterable
    {
        if ($this->isGeneratorClosed ?? false) {
            throw new LogicException('Unable to send the email as its generator is already closed');
        }
        
        if (is_string($this->message)) {
            yield $this->message;
            
            return;
        }
        
        if ($this->message instanceof Generator) {
            $message = '';
            
            foreach ($this->message as $chunk) {
                $message .= $chunk;
                
                yield $chunk;
            }
            
            $this->isGeneratorClosed = ! $this->message->valid();
            $this->message           = $message;
            
            return;
        }
        
        foreach ($this->message as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Array representation of object.
     * 
     * @return array
     */
    public function __serialize(): array
    {
        return [$this->toString()];
    }

    /**
     * Constructs the object.
     * 
     * @param  string  $serialized
     * 
     * @return void
     */
    public function __unserialize(array $serialized): void
    {
        [$this->message] = $serialized;
    }
}