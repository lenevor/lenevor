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

namespace Syscodes\Components\Mail;

use Syscodes\Components\Contracts\View\Factory;
use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Contracts\Mail\Transport;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Contracts\Mail\Mailbox as MailboxContract;
use Syscodes\Components\Contracts\Mail\Mailer as MailerContract;

/**
 * Get the connection with the mail user for send messages.
 */
class Mailer implements MailerContract
{
    /**
     * The event dispatcher instance.
     * 
     * @var \Syscodes\Components\Contracts\Events\Dispatcher|null $events
     */
    protected $events;
    
    /**
     * The global from address and name.
     * 
     * @var array $from
     */
    protected $from;
    
    /**
     * The name that is configured for the mailer.
     * 
     * @var string $name
     */
    protected $name;
    
    /**
     * The global reply-to address and name.
     * 
     * @var array $replyTo
     */
    protected $replyTo;
    
    /**
     * The global return path address.
     * 
     * @var array $returnPath
     */
    protected $returnPath;
    
    /**
     * Get the Transport instance.
     * 
     * @var \Syscodes\Components\Contracts\Mail\Transport $transport
     */
    protected $transport;
    
    /**
     * The view factory instance.
     * 
     * @var \Syscodes\Components\Contracts\View\Factory $views
     */
    protected $views;
    
    /**
     * Constructor. Create a new Mailer class instance.
     * 
     * @param  string  $name
     * @param  \Syscodes\Components\Contracts\View\Factory  $views
     * @param  \Syscodes\Components\Contracts\Mail\Transport  $transport
     * @param  \Syscodes\Components\Contracts\Events\Dispatcher|null  $events
     *
     * @return void
     */
    public function __construct(
        string $name,
        Factory $views,
        Transport $transport,
        Dispatcher $events = null
    ) {
        $this->name = $name;
        $this->views = $views;
        $this->events = $events;
        $this->transport = $transport;
    }
    
    /**
     * Begin the process of mailing a mailbox class instance.
     * 
     * @param  mixed  $users
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Mail\PendingMail
     */
    public function to($users, $name = null)
    {
        if ( ! is_null($name) && is_string($users)) {
            $users = new Address($users, $name);
        }
        
        return (new PendingMail($this))->to($users);
    }
    
    /**
     * Begin the process of mailing a mailable class instance.
     * 
     * @param  mixed  $users
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Mail\PendingMail
     */
    public function cc($users, $name = null)
    {
        if ( ! is_null($name) && is_string($users)) {
            $users = new Address($users, $name);
        }
        
        return (new PendingMail($this))->cc($users);
    }
    
    /**
     * Begin the process of mailing a mailable class instance.
     * 
     * @param  mixed  $users
     * @param  string|null  $name
     * 
     * @return \Syscodes\Components\Mail\PendingMail
     */
    public function bcc($users, $name = null)
    {
        if ( ! is_null($name) && is_string($users)) {
            $users = new Address($users, $name);
        }
        
        return (new PendingMail($this))->bcc($users);
    }
    
    /**
     * Send a new message synchronously using a view.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * 
     * @return \Syscodes\Components\Mail\Helpers\SentMessage|null
     */
    public function sendNow($mailable, array $data = [], $callback = null)
    {
        return $mailable instanceof MailboxContract
                ? $mailable->mailer($this->name)->send($this)
                : $this->send($mailable, $data, $callback);
    }
}