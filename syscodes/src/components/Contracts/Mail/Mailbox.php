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

namespace Syscodes\Components\Contracts\Mail;

/**
 * Allows the send the message using the given mailer.
 */
interface Mailbox
{
    /**
     * Send the message using the given mailer.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Factory|\Syscodes\Components\Contracts\Mail\Mailer  $mailer
     * 
     * @return \Syscodes\Components\Mail\Helpers\SentMessage|null
     */
    public function send($mailer);

    /**
     * Set the sender of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function from($address, $name = null): static;
    
    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function cc($address, $name = null): static;
    
    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function bcc($address, $name = null): static;
    
    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function to($address, $name = null): static;
    
    /**
     * Set the locale of the message.
     * 
     * @param  string  $locale
     * 
     * @return static
     */
    public function locale($locale): static;
    
    /**
     * Set the name of the mailer that should be used to send the message.
     * 
     * @param  string  $mailer
     * 
     * @return static
     */
    public function mailer($mailer): static;
}