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

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Mail\SentMessage;
use Syscodes\Components\Mail\Mailables\Email;
use Syscodes\Components\Mail\Helpers\Envelope;
use Syscodes\Components\Contracts\View\Factory;
use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Mail\Events\MessageSent;
use Syscodes\Components\Contracts\Mail\Transport;
use Syscodes\Components\Contracts\Support\Webable;
use Syscodes\Components\Mail\Events\MessageSending;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Contracts\Mail\Mailer as MailerContract;
use Syscodes\Components\Contracts\Mail\Mailbox as MailboxContract;

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
     * The global to address and name.
     * 
     * @var array $to
     */
    protected $to;
    
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
     * Send a new message with only an HTML part.
     * 
     * @param  string  $html
     * @param  mixed  $callback
     * 
     * @return \Syscodes\Components\Mail\SentMessage|null
     */
    public function html($html, $callback)
    {
        return $this->send(['html' => new Webable($html)], [], $callback);
    }
    
    /**
     * Send a new message with only a raw text part.
     * 
     * @param  string  $text
     * @param  mixed  $callback
     * 
     * @return \Syscodes\Components\Mail\SentMessage|null
     */
    public function raw($text, $callback)
    {
        return $this->send(['raw' => $text], [], $callback);
    }
    
    /**
     * Send a new message with only a plain part.
     * 
     * @param  string  $view
     * @param  array  $data
     * @param  mixed  $callback
     * 
     * @return \Syscodes\Components\Mail\SentMessage|null
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(['text' => $view], $data, $callback);
    }

    /**
     * Send a new message using a view.
     *
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox|string|array  $view
     * @param  array  $data
     * @param  \Closure|string|null  $callback
     * @return \Syscodes\Components\Mail\SentMessage|null
     */
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailboxContract) {
            return $this->sendMailbox($view);
        }

        $data['mailer'] = $this->name;

        [$view, $plain, $raw] = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        if ( ! is_null($callback)) {
            $callback($message);
        }

        $this->addContent($message, $view, $plain, $raw, $data);

        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemove($message);
        }

        $message = $message->getSentMessage();

        if ($this->shouldSendMessage($message, $data)) {
            $sentMessage = $this->sendMessage($message);

            if ($sentMessage) {
                $sentMessage = new SentMessage($sentMessage);

                $this->dispatchSentEvent($sentMessage, $data);

                return $sentMessage;
            }
        }
    }
    
    /**
     * Create a new message instance.
     * 
     * @return \Syscodes\Components\Mail\Message
     */
    protected function createMessage()
    {
        $message = new Message(new Email());
        
        if ( ! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }
        
        if ( ! empty($this->replyTo['address'])) {
            $message->replyTo($this->replyTo['address'], $this->replyTo['name']);
        }
        
        if ( ! empty($this->returnPath['address'])) {
            $message->returnPath($this->returnPath['address']);
        }
        
        return $message;
    }

    /**
     * Send the given mailable.
     *
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox  $mailable
     * @return \Syscodes\Components\Mail\Helpers\SentMessage|null
     */
    protected function sendMailbox(MailboxContract $mailable)
    {
        return $mailable->mailer($this->name)->send($this);
    }
    
    /**
     * Send a new message synchronously using a view.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Mailbox|string|array  $mailable
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
    
    /**
     * Parse the given view name or array.
     * 
     * @param  \Closure|array|string  $view
     * 
     * @return array
     * 
     * @throws \InvalidArgumentException
     */
    protected function parseView($view): array
    {
        if (is_string($view) || $view instanceof Closure) {
            return [$view, null, null];
        }
        
        if (is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];
        }
        
        if (is_array($view)) {
            return 
                [$view['html'] ?? null,
                $view['text'] ?? null,
                $view['raw'] ?? null,
            ];
        }
        
        throw new InvalidArgumentException('Invalid view');
    }
    
    /**
     * Add the content to a given message.
     * 
     * @param  \Syscodes\Components\Mail\Message  $message
     * @param  string  $view
     * @param  string  $plain
     * @param  string  $raw
     * @param  array  $data
     * 
     * @return void
     */
    protected function addContent($message, $view, $plain, $raw, $data): void
    {
        if (isset($view)) {
            $message->html($this->renderView($view, $data) ?: ' ');
        }
        
        if (isset($plain)) {
            $message->text($this->renderView($plain, $data) ?: ' ');
        }
        
        if (isset($raw)) {
            $message->text($raw);
        }
    }
    
    /**
     * Render the given view.
     * 
     * @param  \Closure|string  $view
     * @param  array  $data
     * 
     * @return string
     */
    protected function renderView($view, $data): string
    {
        $view = value($view, $data);
        
        return $view instanceof Webable
                        ? $view->toHtml()
                        : $this->views->make($view, $data)->render();
    }
    
    /**
     * Set the global "to" address on the given message.
     * 
     * @param  \Syscodes\Components\Mail\Message  $message
     * 
     * @return void
     */
    protected function setGlobalToAndRemove($message)
    {
        $message->forgetTo();
        
        $message->to($this->to['address'], $this->to['name'], true);
        
        $message->forgetCc();
        $message->forgetBcc();
    }
    
    /**
     * Determines if the email can be sent.
     * 
     * @param  \Syscodes\Components\Mail\Mailables\Email  $message
     * @param  array  $data
     * 
     * @return bool
     */
    protected function shouldSendMessage($message, $data = []): bool
    {
        if ( ! $this->events) {
            return true;
        }
        
        return $this->events->until(
            new MessageSending($message, $data)
        ) !== false;
    }
    
    /**
     * Dispatch the message sent event.
     * 
     * @param  \Syscodes\Components\Mail\Helpers\SentMessage  $message
     * @param  array  $data
     * 
     * @return void
     */
    protected function dispatchSentEvent($message, $data = []): void
    {
        if ($this->events) {
            $this->events->dispatch(
                new MessageSent($message, $data)
            );
        }
    }
    
    /**
     * Send a Email instance.
     * 
     * @param  \Syscodes\Components\Mail\Mailables\Email  $message
     * 
     * @return \Syscodes\Components\Mail\Helpers\BaseSentMessage|null
     */
    protected function sendMessage(Email $message)
    {
        try {
            return $this->transport->send($message, Envelope::create($message));
        } finally {}
    }
}