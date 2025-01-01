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

use Syscodes\Components\Contracts\Mail\Mailer as MailerContract;
use Syscodes\Components\Contracts\Translation\HasLocalePreferred;
use Syscodes\Components\Contracts\Mail\Mailbox as MailboxContract;

/**
 * Gets from the mailbox of the user data.
 */
class PendingMail
{
    /**
     * The "bcc" recipients of the message.
     * 
     * @var array $bcc
     */
    protected $bcc = [];
    
    /**
     * The "cc" recipients of the message.
     * 
     * @var array $cc
     */
    protected $cc = [];
    
    /**
     * The locale of the message.
     * 
     * @var string $locale
     */
    protected $locale;
    
    /**
     * The mailer instance.
     * 
     * @var \Syscodes\Components\Contracts\Mail\Mailer $mailer
     */
    protected $mailer;
    
    /**
     * The "to" recipients of the message.
     * 
     * @var array $to
     */
    protected $to = [];
    
    /**
     * Constructor. Create a new mailable mailer instance.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailer  $mailer
     * 
     * @return void
     */
    public function __construct(MailerContract $mailer)
    {
        $this->mailer = $mailer;
    }
    
    /**
     * Set the locale of the message.
     * 
     * @param  string  $locale
     * 
     * @return static
     */
    public function locale($locale): static
    {
        $this->locale = $locale;
        
        return $this;
    }
    
    /**
     * Set the recipients of the message.
     * 
     * @param  mixed  $users
     * 
     * @return static
     */
    public function to($users): static
    {
        $this->to = $users;
        
        if ( ! $this->locale && $users instanceof HasLocalePreferred) {
            $this->locale($users->preferredLocale());
        }
        
        return $this;
    }
    
    /**
     * Set the recipients of the message.
     * 
     * @param  mixed  $users
     * 
     * @return static
     */
    public function cc($users): static
    {
        $this->cc = $users;
        
        return $this;
    }
    
    /**
     * Set the recipients of the message.
     * 
     * @param  mixed  $users
     * 
     * @return static
     */
    public function bcc($users): static
    {
        $this->bcc = $users;
        
        return $this;
    }
    
    /**
     * Send a new mailable message instance.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox  $mailable
     * 
     * @return \Syscodes\Components\Mail\SentMessage|null
     */
    public function send(MailboxContract $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }
    
    /**
     * Send a new mailable message instance synchronously.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox  $mailable
     * 
     * @return \Syscodes\Components\Mail\SentMessage|null
     */
    public function sendNow(MailboxContract $mailable)
    {
        return $this->mailer->sendNow($this->fill($mailable));
    }
    
    /**
     * Populate the mailable with the addresses.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox $mailable
     * 
     * @return \Syscodes\Components\Mail\Mailbox
     */
    protected function fill(MailboxContract $mailable)
    {
        return take($mailable->to($this->to)
                    ->cc($this->cc)
                    ->bcc($this->bcc), function (MailboxContract $mailable) {
                        if ($this->locale) {
                            $mailable->locale($this->locale);
                        }
               });
    }
}