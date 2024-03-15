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

namespace Syscodes\Components\Mail\Events;

use Exception;
use Syscodes\Components\Mail\Helpers\SentMessage;

/**
 * Get the message sent to mail.
 */
class MessageSent
{
    /**
     * The message data.
     * 
     * @var array $data
     */
    public $data;
    
    /**
     * The message that was sent.
     * 
     * @var \Syscodes\Components\Mail\Helpers\SentMessage $sent
     */
    public $sent;
    
    /**
     * Constructor. Create a new event class instance.
     * 
     * @param  \Syscodes\Components\Mail\Helpers\SentMessage  $message
     * @param  array  $data
     * 
     * @return void
     */
    public function __construct(SentMessage $message, array $data = [])
    {
        $this->sent = $message;
        $this->data = $data;
    }
    
    /**
     * Get the serializable representation of the object.
     * 
     * @return array
     */
    public function __serialize()
    {
        return [
            'sent' => $this->sent,
            'data' => base64_encode(serialize($this->data)),
        ];
    }
    
    /**
     * Marshal the object from its serialized data.
     * 
     * @param  array  $data
     * 
     * @return void
     */
    public function __unserialize(array $data)
    {
        $this->sent = $data['sent'];
        $this->data = unserialize(base64_decode($data['data']));
    }
    
    /**
     * Magic method.
     * 
     * Dynamically get the original message.
     * 
     * @param  string  $key
     * 
     * @return mixed
     * 
     * @throws \Exception
     */
    public function __get($key)
    {
        if ($key === 'message') {
            return $this->sent->getOriginalMessage();
        }
        
        throw new Exception('Unable to access undefined property on '.__CLASS__.': '.$key);
    }
}