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
namespace Syscodes\Components\Mail\Transport;

use InvalidArgumentException;

/**
 * Get the domain transport of a email.
 */
final class DomainTransport
{
    /**
     * Get the host.
     * 
     * @var string $host
     */
    protected string $host;
    
    /**
     * Get the options.
     * 
     * @var array $options
     */
    protected array $options;
    
    /**
     * Get the password.
     * 
     * @var string|null $password
     */
    protected ?string $password;
    
    /**
     * Get the port.
     * 
     * @var int|null $port
     */
    protected ?int $port;

    /**
     * Get the scheme.
     * 
     * @var string $scheme
     */
    protected string $scheme;
    
    /**
     * Get the user.
     * 
     * @var string|null $user
     */
    protected ?string $user;

    /**
     * Constructor. Create a new DomainTransport class instance.
     * 
     * @param  string  $scheme
     * @param  string  $host
     * @param  string|null  $user
     * @param  string|null  $password
     * @param  int|null  $port
     * @param  array  $options
     * 
     * @return void
     */
    public function __construct(
        string $scheme,
        string $host,
        string $user = null,
        string $password = null,
        int $port = null,
        array $options = []
    ) {
        $this->scheme   = $scheme;
        $this->host     = $host;
        $this->user     = $user;
        $this->password = $password;
        $this->port     = $port;
        $this->options  = $options;
    }

    /**
     * Gets the string of URL from domain.
     * 
     * @param  string  $domain
     * 
     * @return self
     */
    public static function fromString(string $domain): self
    {
        if (false === $params = parse_url($domain)) {
            throw new InvalidArgumentException('The mailer DSN is invalid');
        }
        
        if ( ! isset($params['scheme'])) {
            throw new InvalidArgumentException('The mailer DSN must contain a scheme');
        }
        
        if ( ! isset($params['host'])) {
            throw new InvalidArgumentException('The mailer DSN must contain a host');
        }
        
        $user     = '' !== ($params['user'] ?? '') ? rawurldecode($params['user']) : null;
        $password = '' !== ($params['pass'] ?? '') ? rawurldecode($params['pass']) : null;
        $port     = $params['port'] ?? null;
        
        parse_str($params['query'] ?? '', $query);
        
        return new self($params['scheme'], $params['host'], $user, $password, $port, $query);
    }

    /**
     * Gets thhe scheme. 
     * 
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Gets the host.
     * 
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }
    
    /**
     * Gets the user.
     * 
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }
    
    /**
     * Gets the password.
     * 
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    /**
     * Gets the port.
     * 
     * @return string|null
     */
    public function getPort(int $default = null): ?int
    {
        return $this->port ?? $default;
    }

    /**
     * Gets the options.
     * 
     * @return mixed
     */
    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
}