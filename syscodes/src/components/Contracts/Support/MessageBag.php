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

namespace Syscodes\Components\Contracts\Support;

use Countable;

/**
 * Gets the messages to the bag.
 */
interface MessageBag extends Arrayable, Countable
{
    /**
     * Get the keys present in the message bag.
     * 
     * @return array
     */
    public function keys();
    
    /**
     * Add a message to the bag.
     * 
     * @param  string  $key
     * @param  string  $message 
     * @return $this
     */
    public function add($key, $message): static;
    
    /**
     * Merge a new array of messages into the bag.
     * 
     * @param  \Syscodes\Components\Contracts\Support\MessageProvider|array  $messages 
     * @return $this
     */
    public function merge($messages): static;
    
    /**
     * Determine if messages exist for a given key.
     * 
     * @param  string|array  $key 
     * @return bool
     */
    public function has($key): bool;
    
    /**
     * Get the first message from the bag for a given key.
     * 
     * @param  string|null  $key
     * @param  string|null  $format 
     * @return string
     */
    public function first($key = null, $format = null);
    
    /**
     * Get all of the messages from the bag for a given key.
     * 
     * @param  string  $key
     * @param  string|null  $format 
     * @return array
     */
    public function get($key, $format = null);
    
    /**
     * Get all of the messages for every key in the bag.
     * 
     * @param  string|null  $format 
     * @return array
     */
    public function all($format = null);
    
    /**
     * Get the raw messages in the container.
     * 
     * @return array
     */
    public function getMessages();
    
    /**
     * Get the default message format.
     * 
     * @return string
     */
    public function getFormat(): string;
    
    /**
     * Set the default message format.
     * 
     * @param  string  $format 
     * @return $this
     */
    public function setFormat($format = ':message'): static;
    
    /**
     * Determine if the message bag has any messages.
     * 
     * @return bool
     */
    public function isEmpty(): bool;
    
    /**
     * Determine if the message bag has any messages.
     * 
     * @return bool
     */
    public function isNotEmpty(): bool;
    
    /**
     * Determine if the message bag has any messages.
     * 
     * @return bool
     */
    public function any(): bool;
    
    /**
     * Get the number of messages in the container.
     * 
     * @return int
     */
    public function count(): int;    
}