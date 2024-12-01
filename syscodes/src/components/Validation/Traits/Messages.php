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

namespace Syscodes\Components\Validation\Traits;

/**
 * Get the messsages.
 */
trait Messages
{
    /** 
     * The message implementation.
     * 
     * @var array $messages
     */
    protected $messages = [];
    
    /**
     * Given $key and $message to set message
     * 
     * @param  mixed  $key
     * @param  mixed  $message
     * 
     * @return void
     */
    public function setMessage(string $key, string $message): void
    {
        $this->messages[$key] = $message;
    }
    
    /**
     * Given $messages and set multiple messages.
     * 
     * @param  array  $messages
     * 
     * @return void
     */
    public function setMessages(array $messages): void
    {
        $this->messages = array_merge($this->messages, $messages);
    }
    
    /**
     * Given message from given $key.
     * 
     * @param  string  $key
     * 
     * @return string
     */
    public function getMessage(string $key): string
    {
        return array_key_exists($key, $this->messages) ? $this->messages[$key] : $key;
    }
    
    /**
     * Get all $messages
     * 
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}