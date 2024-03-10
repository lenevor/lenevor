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

use ReflectionClass;
use ReflectionProperty;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Core\Application;
use Syscodes\Components\Support\WebString;
use Syscodes\Components\Support\Collection;
use Syscodes\Components\Mail\Mailables\Address;
use Syscodes\Components\Support\Traits\Macroable;
use Syscodes\Components\Support\Traits\Localizable;
use Syscodes\Components\Contracts\Support\Renderable;
use Syscodes\Components\Support\Traits\ForwardsCalls;
use Syscodes\Components\Contracts\Mail\Factory as MailFactory;
use Syscodes\Components\Contracts\Mail\Mailbox as MailboxContract;

/**
 * Allows the send the message using the given mailer.
 */
class Mailbox implements MailboxContract, Renderable
{
    use Macroable,
        Localizable,
        ForwardsCalls;
        
    /**
     * The callback that should be invoked while building the view data.
     * 
     * @var callable $viewDataCallback
     */
    public static $viewDataCallback;

    /**
     * The attachments for the message.
     * 
     * @var array $attachments
     */
    public $attachments = [];
    
    /**
     * The "bcc" recipients of the message.
     * 
     * @var array $bcc
     */
    public $bcc = [];
    
    /**
     * The callbacks for the message.
     * 
     * @var array $callbacks
     */
    public $callbacks = [];
    
    /**
     * The "cc" recipients of the message.
     * 
     * @var array $cc
     */
    public $cc = [];
    
    /**
     * The attachments from a storage disk.
     * 
     * @var array $diskAttachments
     */
    public $diskAttachments = [];
    
    /**
     * The person the message is from.
     * 
     * @var array $from
     */
    public $from = [];
    
    /**
     * The HTML to use for the message.
     * 
     * @var string $html
     */
    protected $html;
    
    /**
     * The locale of the message.
     * 
     * @var string $locale
     */
    public $locale;
    
    /**
     * The name of the mailer that should send the message.
     * 
     * @var string $mailer
     */
    public $mailer;
    
    /**
     * The metadata for the message.
     * 
     * @var array $metadata
     */
    protected $metadata = [];
    
    /**
     * The raw attachments for the message.
     * 
     * @var array $rawAttachments
     */
    public $rawAttachments = [];
    
    /**
     * The "reply to" recipients of the message.
     * 
     * @var array $replyTo
     */
    public $replyTo = [];
    
    /**
     * The subject of the message.
     * 
     * @var string $subject
     */
    public $subject;
    
    /**
     * The tags for the message.
     * 
     * @var array $tags
     */
    protected $tags = [];
    
    /**
     * The plain text view to use for the message.
     * 
     * @var string $textView
     */
    public $textView;
    
    /**
     * The name of the theme that should be used when formatting the message.
     * 
     * @var string|null $theme
     */
    public $theme;
    
    /**
     * The "to" recipients of the message.
     * 
     * @var array $to
     */
    public $to = [];
    
    /**
     * The view to use for the message.
     * 
     * @var string $view
     */
    public $view;
    
    /**
     * The view data for the message.
     * 
     * @var array $viewData
     */
    public $viewData = [];

    /**
     * Send the message using the given mailer.
     * 
     * @param  \Syscodes\Components\Contracts\Mail\Factory|\Syscodes\Components\Contracts\Mail\Mailer  $mailer
     * 
     * @return \Syscodes\Components\Mail\Helpers\SentMessage|null
     */
    public function send($mailer)
    {
        return $this->assignLocale($this->locale, function () use ($mailer) {
                    $mailer = $mailer instanceof MailFactory
                            ? $mailer->mailer($this->mailer)
                            : $mailer;
                
                return $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
                            $this->buildFrom($message)
                                 ->buildRecipients($message)
                                 ->buildSubject($message);
                });
        });
    }

    /**
     * Get the evaluated contents of the object.
     * 
     * @return string
     */
    public function render()
    {
        return $this->assignLocale($this->locale, function () {
            return Application::getInstance()->make('mailer')->render(
                $this->buildView(), $this->buildViewData()
            );
        });
    }

    /**
     * Add the sender to the message.
     * 
     * @param  \Syscodes\Components\Mail\Message  $message
     * 
     * @return static
     */
    protected function buildFrom($message): static
    {
        if ( ! empty($this->from)) {
            $message->from($this->from[0]['address'], $this->from[0]['name']);
        }
        
        return $this;
    }
    
    /**
     * Add all of the recipients to the message.
     * 
     * @param  \Syscodes\Components\Mail\Message  $message
     * 
     * @return static
     */
    protected function buildRecipients($message): static
    {
        foreach (['to', 'cc', 'bcc', 'replyTo'] as $type) {
            foreach ($this->{$type} as $recipient) {
                $message->{$type}($recipient['address'], $recipient['name']);
            }
        }
        
        return $this;
    }
    
    /**
     * Build the view for the message.
     * 
     * @return array|string
     * 
     * @throws \ReflectionException
     */
    protected function buildView(): array|string
    {
        if (isset($this->html)) {
            return array_filter([
                'html' => new WebString($this->html),
                'text' => $this->textView ?? null,
            ]);
        }
        
        if (isset($this->view, $this->textView)) {
            return [$this->view, $this->textView];
        } elseif (isset($this->textView)) {
            return ['text' => $this->textView];
        }
        
        return $this->view;
    }
    
    /**
     * Build the view data for the message.
     * 
     * @return array
     * 
     * @throws \ReflectionException
     */
    public function buildViewData(): array
    {
        $data = $this->viewData;
        
        if (static::$viewDataCallback) {
            $data = array_merge($data, call_user_func(static::$viewDataCallback, $this));
        }
        
        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isInitialized($this) && $property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }
        
        return $data;
    }
    
    /**
     * Set the subject for the message.
     * 
    * @param  \Syscodes\Components\Mail\Message  $message
     * 
     * @return static
     */
    protected function buildSubject($message): static
    {
        if ($this->subject) {
            $message->subject($this->subject);
        } else {
            $message->subject(Str::title(Str::snake(class_basename($this), ' ')));
        }
        
        return $this;
    }

    /**
     * Set the view and view data for the message.
     * 
     * @param  string  $view
     * @param  array  $data
     * 
     * @return static
     */
    public function view($view, array $data = []): static
    {
        $this->view     = $view;
        $this->viewData = array_merge($this->viewData, $data);
        
        return $this;
    }
    
    /**
     * Set the rendered HTML content for the message.
     * 
     * @param  string  $html
     * 
     * @return static
     */
    public function html($html): static
    {
        $this->html = $html;
        
        return $this;
    }
    
    /**
     * Set the plain text view for the message.
     * 
     * @param  string  $textView
     * @param  array  $data
     * 
     * @return static
     */
    public function text($textView, array $data = []): static
    {
        $this->textView = $textView;
        $this->viewData = array_merge($this->viewData, $data);
        
        return $this;
    }
    
    /**
     * Set the sender of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function from($address, $name = null): static
    {
        return $this->setAddress($address, $name, 'from');
    }
    
    /**
     * Determine if the given recipient is set on the mailabox.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return bool
     */
    public function hasFrom($address, $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'from');
    }
    
    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function to($address, $name = null): static
    {
        return $this->setAddress($address, $name, 'to');
    }

    /**
     * Determine if the given recipient is set on the mailable.
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @return bool
     */
    public function hasTo($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'to');
    }

    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function cc($address, $name = null): static
    {
        return $this->setAddress($address, $name, 'cc');
    }
    
    /**
     * Determine if the given recipient is set on the mailable.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return bool
     */
    public function hasCc($address, $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'cc');
    }
    
    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function bcc($address, $name = null): static
    {
        return $this->setAddress($address, $name, 'bcc');
    }
    
    /**
     * Determine if the given recipient is set on the mailable.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return bool
     */
    public function hasBcc($address, $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'bcc');
    }
    
    /**
     * Set the "reply to" address of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return static
     */
    public function replyTo($address, $name = null): static
    {
        return $this->setAddress($address, $name, 'replyTo');
    }
    
    /**
     * Determine if the given replyTo is set on the mailable.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return bool
     */
    public function hasReplyTo($address, $name = null): bool
    {
        return $this->hasRecipient($address, $name, 'replyTo');
    }
    
    /**
     * Set the recipients of the message.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * 
     * @return static
     */
    protected function setAddress($address, $name = null, $property = 'to'): static
    {
        if (empty($address)) {
            return $this;
        }
        
        foreach ($this->addressesToArray($address, $name) as $recipient) {
            $recipient = $this->normalizeRecipient($recipient);
            
            $this->{$property}[] = [
                'name'    => $recipient->name ?? null,
                'address' => $recipient->email,
            ];
        }
        
        $this->{$property} = collect($this->{$property})
             ->reverse()
             ->unique('address')
             ->reverse()
             ->values()
             ->all();
        
        return $this;
    }
    
    /**
     * Convert the given recipient arguments to an array.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * 
     * @return array
     */
    protected function addressesToArray($address, $name): array
    {
        if ( ! is_array($address) && ! $address instanceof Collection) {
            $address = is_string($name) ? [['name' => $name, 'email' => $address]] : [$address];
        }
        
        return $address;
    }
    
    /**
     * Convert the given recipient into an object.
     * 
     * @param  mixed  $recipient
     * 
     * @return object
     */
    protected function normalizeRecipient($recipient)
    {
        if (is_array($recipient)) {
            if (array_values($recipient) === $recipient) {
                return (object) array_map(function ($email) {
                    return compact('email');
                }, $recipient);
            }
            
            return (object) $recipient;
        } elseif (is_string($recipient)) {
            return (object) ['email' => $recipient];
        } elseif ($recipient instanceof Address) {
            return (object) ['email' => $recipient->getAddress(), 'name' => $recipient->getName()];
        }
        
        return $recipient;
    }
    
    /**
     * Determine if the given recipient is set on the mailable.
     * 
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * 
     * @return bool
     */
    protected function hasRecipient($address, $name = null, $property = 'to'): bool
    {
        if (empty($address)) {
            return false;
        }
        
        $expected = $this->normalizeRecipient(
            $this->addressesToArray($address, $name)[0]
        );
        
        $expected = [
            'name' => $expected->name ?? null,
            'address' => $expected->email,
        ];
        
        return collect($this->{$property})->contains(function ($actual) use ($expected) {
            if ( ! isset($expected['name'])) {
                return $actual['address'] == $expected['address'];
            }
            
            return $actual == $expected;
        });
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
        return $this;
    }
    
    /**
     * Set the name of the mailer that should be used to send the message.
     * 
     * @param  string  $mailer
     * 
     * @return static
     */
    public function mailer($mailer): static
    {
        $this->mailer = $mailer;

        return $this;
    }
    
    /**
     * Set the view data for the message.
     * 
     * @param  string|array  $key
     * @param  mixed  $value
     * 
     * @return static
     */
    public function with($key, $value = null): static
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Magic Method.
     * 
     * Dynamically bind parameters to the message.
     * 
     * @param  string  $method
     * @param  array  $parameters
     * 
     * @return static
     * 
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::camelcase(substr($method, 4)), $parameters[0]);
        }
        
        static::throwBadMethodCallException($method);
    }
}