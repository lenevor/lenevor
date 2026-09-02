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

use JsonSerializable;
use Stringable;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Contracts\Support\Arrayable;
use Syscodes\Components\Contracts\Support\Jsonable;
use Syscodes\Components\Contracts\Support\MessageBag as MessageBagContract;
use Syscodes\Components\Contracts\Support\MessageProvider;

/**
 * Allows the messages into the bag.
 */
class MessageBag implements Jsonable, JsonSerializable, MessageBagContract, MessageProvider, Stringable
{
    /**
     * All of the registered messages.
     * 
     * @var array
     */
    protected $messages = [];
    
    /**
     * Default format for message output.
     * 
     * @var string
     */
    protected $format = ':message';

    /**
     * Constructor. Create a new MessageBag class instance.
     * 
     * @param  array<string, Arrayable|string|array<string>>  $messages 
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
     * @return array<string>
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
     * Add a message to the message bag if the given conditional is "true".
     *
     * @param  bool  $boolean
     * @param  string  $key
     * @param  string  $message
     * @return static
     */
    public function addIf($boolean, $key, $message): static
    {
        return $boolean ? $this->add($key, $message) : $this;
    }
    
    /**
     * Determine if a key and message combination already exists.
     * 
     * @param  string  $key
     * @param  string  $message 
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
     * @return static
     */
    public function merge($messages): static
    {
        if ($messages instanceof MessageProvider) {
            $messages = $messages->getMessageBag()->getMessages();
        }
        
        $this->messages = array_merge_recursive($this->messages, $messages);
        
        return $this;
    }
    
    /**
     * Determine if messages exist for a given key.
     * 
     * @param  string|array  $key 
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
        
        foreach ($keys as $k) {
            if ($this->first($k) === '') {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Determine if messages exist for any of the given keys.
     *
     * @param  array|string|null  $keys
     * @return bool
     */
    public function hasAny($keys = []): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        foreach ($keys as $key) {
            if ($this->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if messages don't exist for all of the given keys.
     *
     * @param  array|string|null  $key
     * @return bool
     */
    public function missing($key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        return ! $this->hasAny($keys);
    }
    
    /**
     * Get the first message from the bag for a given key.
     * 
     * @param  string|null  $key
     * @param  string|null  $format 
     * @return string
     */
    public function first($key = null, $format = null)
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
     * @return array<string>|array<string, array<string>>
     */
    public function get($key, $format = null): array
    {
        $format = $this->checkFormat($format);
        
         // If the message exists in the message bag, we will transform it and return
        // the message.
        if (array_key_exists($key, $this->messages)) {
            return $this->transform(
                $this->messages[$key], $format, $key
            );
        }

        if (str_contains($key, '*')) {
            return $this->getMessagesForWildcardKey($key, $format);
        }
        
        return [];
    }

    /**
     * Get the messages for a wildcard key.
     *
     * @param  string  $key
     * @param  string|null  $format
     * @return array<string, array<string>>
     */
    protected function getMessagesForWildcardKey($key, $format): array
    {
        return (new Collection($this->messages))
            ->filter(fn ($messages, $messageKey) => Str::is($key, $messageKey))
            ->map(function ($messages, $messageKey) use ($format) {
                return $this->transform($messages, $this->checkFormat($format), $messageKey);
            })
            ->all();
    }
    
    /**
     * Get all of the messages for every key in the bag.
     * 
     * @param  string|null  $format 
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
     * Get all of the unique messages for every key in the message bag.
     *
     * @param  string|null  $format
     * @return array
     */
    public function unique($format = null): array
    {
        return array_unique($this->all($format));
    }

    /**
     * Remove a message from the message bag.
     *
     * @param  string  $key
     * @return static
     */
    public function erase($key): static
    {
        unset($this->messages[$key]);

        return $this;
    }
    
    /**
     * Format an array of messages.
     * 
     * @param  array<string>  $messages
     * @param  string  $format
     * @param  string  $messageKey 
     * @return array<string>
     */
    protected function transform($messages, $format, $messageKey): array
    {
        if ($format == ':message') {
            return (array) $messages;
        }

        return (new collection((array) $messages))
            ->map(function ($message) use ($format, $messageKey) {
                return str_replace([':message', ':key'], [$message, $messageKey], $format);
            })->all();
    }
    
    /**
     * Get the appropriate format based on the given format.
     * 
     * @param  string  $format 
     * @return string
     */
    protected function checkFormat($format): string
    {
        return $format ?: $this->format;
    }
    
    /**
     * Get the raw messages in the message bag.
     * 
     * @return array<string, array<string>>
     */
    public function messages(): array
    {
        return $this->messages;
    }
    
    /**
     * Get the raw messages in the container.
     * 
     * @return array<string, array<string>>
     */
    public function getMessages(): array
    {
        return $this->messages();
    }
    
    /**
     * Get the messages for the instance.
     * 
     * @return \Syscodes\Components\Support\MessageBag
     */
    public function getMessageBag()
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
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object to pretty print formatted JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toPrettyJson(int $options = 0): string
    {
        return $this->toJson(JSON_PRETTY_PRINT | $options);
    }

    /**
     * Magic method.
     * 
     * Convert the message bag to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }    
}