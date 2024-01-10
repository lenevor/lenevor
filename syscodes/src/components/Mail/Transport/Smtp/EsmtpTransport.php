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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Mail\Transport\Smtp;

use Psr\Log\LoggerInterface;
use Syscodes\Components\Contracts\Events\Dispatcher;
use Syscodes\Components\Mail\Transport\Smtp\SocketStream;
use Syscodes\components\Constracts\Mail\Auth\Authenticator;

/**
 * Sends Emails over SMTP with ESMTP support.
 */
class EsmtpTransport extends SmtpTransport
{
    /**
     * Get the authenticators.
     * 
     * @var array $authenticators
     */
    protected array $authenticators = [];

    /**
     * Get the password.
     * 
     * @var string $password
     */
    protected string $password = '';

    /**
     * Get the username.
     * 
     * @var string $username
     */
    protected string $username = '';
    
    /**
     * Constructor. Create a new EsmtpTransport class instance.
     * 
     * @param  string  $host
     * @param  int  $port
     * @param  bool|null  $tls
     * @param  Dispatcher|null  $dispatcher
     * @param  LoggerInterface|null  $logger
     * @param  AbstractStream|null  $stream
     * @param  array|null  $authenticators
     * 
     * @return void
     */
    public function __construct(
        string $host = 'localhost',
        int $port = 0,
        bool $tls = null,
        Dispatcher $dispatcher = null,
        LoggerInterface $logger = null,
        AbstractStream $stream = null,
        array $authenticators = null
    ) {
        parent::__construct($stream, $dispatcher, $logger);
        
        if (null === $authenticators) {
            $authenticators = [
                new LoginAuthenticator(),
                new PlainAuthenticator(),
            ];
        }
        
        $this->setAuthenticators($authenticators);
        
        /** @var SocketStream $stream */
        $stream = $this->getStream();
        
        if (null === $tls) {
            if (465 === $port) {
                $tls = true;
            } else {
                $tls = defined('OPENSSL_VERSION_NUMBER') && 0 === $port && 'localhost' !== $host;
            }
        }
        
        if ( ! $tls) {
            $stream->disableTls();
        }
        
        if (0 === $port) {
            $port = $tls ? 465 : 25;
        }
        
        $stream->setHost($host);
        $stream->setPort($port);
    }
    
    /**
     * Sets the username.
     * 
     * @param  string  $username
     * 
     * @return static
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;
        
        return $this;
    }
    
    /**
     * Get the username.
     * 
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
    
    /**
     * Sets the password.
     * 
     * @param  string  $password
     * 
     * @return static
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        
        return $this;
    }
    
    /**
     * Get the password.
     * 
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the autenticators.
     * 
     * @param  array  $authenticators
     * 
     * @return void
     */    
    public function setAuthenticators(array $authenticators): void
    {
        $this->authenticators = [];
        
        foreach ($authenticators as $authenticator) {
            $this->addAuthenticator($authenticator);
        }
    }
    
    /**
     * Adds a authenticator in an array.
     * 
     * @param  Authenticator  $authenticator
     * 
     * @return void
     */
    public function addAuthenticator(Authenticator $authenticator): void
    {
        $this->authenticators[] = $authenticator;
    }
}