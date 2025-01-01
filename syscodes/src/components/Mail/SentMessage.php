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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail;

use Syscodes\Components\Mail\Helpers\BaseSentMessage;
use Syscodes\Components\Support\Traits\ForwardsCalls;

/**
 * Allows the sent of message at recipient's email.
 */
class SentMessage
{
    use ForwardsCalls;
    
    /**
     * The base SentMessage instance.
     * 
     * @var \Syscodes\Components\Mail\Helpers\BaseSentMessage $sentMessage
     */
    protected $sentMessage;
    
    /**
     * Constructor. Create a new SentMessage class instance.
     * 
     * @param  \Syscodes\Components\Mail\Helpers\BaseSentMessage  $sentMessage
     * 
     * @return void
     */
    public function __construct(BaseSentMessage $sentMessage)
    {
        $this->sentMessage = $sentMessage;
    }
    
    /**
     * Get the underlying base Email instance.
     * 
     * @return \Syscodes\Components\Mail\Helpers\BaseSentMessage
     */
    public function getSentMessage()
    {
        return $this->sentMessage;
    }
    
    /**
     * Magic method.
     * 
     * Dynamically pass missing methods to the base instance.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->sentMessage, $method, $parameters);
    }
}