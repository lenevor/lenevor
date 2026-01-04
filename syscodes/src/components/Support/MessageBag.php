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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support;

use Countable;
use JsonSerializable;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\MessageBag as MessageBagContract;

/**
 * Allows the messages into the bag.
 */
class MessageBag implements Arrayable, Countable, JsonSerializable, MessageBagContract
{
    /**
     * All of the registered messages.
     * 
     * @var array $messages
     */
    protected $messages = [];
    
    /**
     * Default format for message output.
     * 
     * @var string $format
     */
    protected $format = ':message';

    /**
     * Constructor. Create a new MessageBag class instance.
     * 
     * @param  array  $messages
     * 
     * @return void
     */
    public function __construct(array $messages = [])
    {
        foreach ($messages as $key => $value) {
            $value = $value instanceof Arrayable ? $value->toArray() : (array) $value;
            
            $this->messages[$key] = array_unique($value); 
        }        
    }
    
    /**
     * Get the keys present in the message bag.
     * 
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->messages);
    }
    
    /**
     * Add a message to the bag.
     * 
     * @param  string  $key
     * @param  string  $message
     * 
     * @return static
     */
    public function add($key, $message): static
    {
        if ($this->isUnique($key, $message)) {
            $this->messages[$key][] = $message;
        }
        
        return $this;
    }
    
    /**
     * Determine if a key and message combination already exists.
     * 
     * @param  string  $key
     * @param  string  $message
     * 
     * @return bool
     */
    protected function isUnique($key, $message): bool
    {
        $messages = (array) $this->messages;
        
        return ! isset($messages[$key]) || ! in_array($message, $messages[$key]);
    }
    
    /**
     * Merge a new array of messages into the bag.
     * 
     * @param  \Syscodes\Components\Contracts\Support\MessageProvider|array  $messages
     * 
     * @return static
     */
    public function merge($messages): static
    {
        $this->messages = array_merge_recursive($this->messages, $messages);
        
        return $this;
    }
    
    /**
     * Determine if messages exist for a given key.
     * 
     * @param  string|array  $key
     * 
     * @return bool
     */
    public function has($key): bool
    {
        if ($this->isEmpty()) {
            return false;
        }
        
        if (is_null($key)) {
            return $this->any();
        }
        
        $keys = is_array($key) ? $key : func_get_args();
        
        foreach ($keys as $key) {
            if ($this->first($key) === '') {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get the first message from the bag for a given key.
     * 
     * @param  string|null  $key
     * @param  string|null  $format
     * 
     * @return string
     */
    public function first($key = null, $format = null): string
    {
        $messages = is_null($key) ? $this->all($format) : $this->get($key, $format);
        
        $firstMessage = Arr::first($messages, null, '');
        
        return is_array($firstMessage) ? Arr::first($firstMessage) : $firstMessage;
    }
    
    /**
     * Get all of the messages from the bag for a given key.
     * 
     * @param  string  $key
     * @param  string|null  $format
     * 
     * @return array
     */
    public function get($key, $format = null): array
    {
        $format = $this->checkFormat($format);
        
        if (array_key_exists($key, $this->messages)) {
            return $this->transform($this->messages[$key], $format, $key);
        }
        
        return [];
    }
    
    /**
     * Get all of the messages for every key in the bag.
     * 
     * @param  string|null  $format
     * 
     * @return array
     */
    public function all($format = null): array
    {
        $format = $this->checkFormat($format);
        
        $all = [];
        
        foreach ($this->messages as $key => $messages) {
            $all = array_merge($all, $this->transform($messages, $format, $key));
        }
        
        return $all;
    }
    
    /**
     * Format an array of messages.
     * 
     * @param  array   $messages
     * @param  string  $format
     * @param  string  $messageKey
     * 
     * @return array
     */
    protected function transform($messages, $format, $key): array
    {
        if ($format == ':message') {
            return (array) $messages;
        }

        return collect((array) $messages)
               ->map(function ($message) use ($format, $key) {
                    return str_replace([':message', ':key'], [$message, $key], $format);
               })->all();
    }
    
    /**
     * Get the appropriate format based on the given format.
     * 
     * @param  string  $format
     * 
     * @return string
     */
    protected function checkFormat($format): string
    {
        return $format ?: $this->format;
    }
    
    /**
     * Get the raw messages in the message bag.
     * 
     * @return array
     */
    public function messages(): array
    {
        return $this->messages;
    }
    
    /**
     * Get the raw messages in the container.
     * 
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages();
    }
    
    /**
     * Get the messages for the instance.
     * 
     * @return static
     */
    public function getMessageBag(): static
    {
        return $this;
    }
    
    /**
     * Get the default message format.
     * 
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }
    
    /**
     * Set the default message format.
     * 
     * @param  string  $format
     * 
     * @return static
     */
    public function setFormat($format = ':message'): static
    {
        $this->format = $format;
        
        return $this;
    }
    
    /**
     * Determine if the message bag has any messages.
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return ! $this->any();
    }
    
    /**
     * Determine if the message bag has any messages.
     * 
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->any();
    }
    
    /**
     * Determine if the message bag has any messages.
     * 
     * @return bool
     */
    public function any(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Get the number of messages in the message bag.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getMessages();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
    
    /**
     * Convert the object to its JSON representation.
     * 
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Magic method.
     * 
     * Convert the message bag to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }    
}