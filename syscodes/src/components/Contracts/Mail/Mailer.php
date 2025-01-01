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

namespace Syscodes\Components\Contracts\Mail;

/**
 * Gets the mailer using the process of mailing.
 */
interface Mailer
{
    /**
     * Begin the process of mailing a mailable class instance.
     * 
     * @param  mixed  $users
     * 
     * @return \Syscodes\Components\Mail\PendingMail
     */
    public function to($users);
    
    /**
     * Begin the process of mailing a mailable class instance.
     * 
     * @param  mixed  $users
     * 
     * @return \Syscodes\Components\Mail\PendingMail
     */
    public function bcc($users);
    
    /**
     * Send a new message with only a raw text part.
     * 
     * @param  string  $text
     * @param  mixed  $callback
     * 
     * @return \Syscodes\Components\Mail\Helpers\BaseSentMessage|null
     */
    public function raw($text, $callback);
    
    /**
     * Send a new message using a view.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * 
     * @return \Syscodes\Components\Mail\Helpers\BaseSentMessage|null
     */
    public function send($view, array $data = [], $callback = null);
    
    /**
     * Send a new message synchronously using a view.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox|string|array  $mailable
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * 
     * @return \Syscodes\Components\Mail\Helpers\BaseSentMessage|null
     */
    public function sendNow($mailable, array $data = [], $callback = null);
}