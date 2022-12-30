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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Http;

use DateTimeInterface;
use InvalidArgumentException;
use Syscodes\Components\Http\Concerns\BuildCookieHeader;

/**
 * Represents and execute a cookie.
 */
class Cookie
{
    use BuildCookieHeader;
    
    public const SAMESITE_RESTRICTION_NONE   = 'none';
    public const SAMESITE_RESTRICTION_LAX    = 'lax';
    public const SAMESITE_RESTRICTION_STRICT = 'strict';
    
    protected const SYS_RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";
    protected const SYS_RESERVED_CHARS_FROM = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];
    protected const SYS_RESERVED_CHARS_TO   = ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'];

    /**
     * Get the name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * Get the domain.
     * 
     * @var string $domain
     */
    protected $domain;

    /**
     * Get the time to expire a cookie.
     * 
     * @var int $expire
     */
    protected $expire;

    /**
     * Get the http only to secure.
     * 
     * @var bool $httpOnly
     */
    protected $httpOnly;

    /**
     * Get the path.
     * 
     * @var string $path
     */
    protected $path;

    /**
     * Checks if the cookie value should be sent.
     * 
     * @var bool $raw
     */
    protected $raw;

    /**
     * Get the samesite.
     * 
     * @var string $sameSite
     */
    protected $sameSite = null;

    /**
     * Indicates that the cookie should be sent back by the client over 
     * secure HTTPS connections only.
     * 
     * @var bool $secure
     */
    protected $secure;

    /**
     * The default value for the secure in the cookies.
     * 
     * @var bool $secureDefault
     */
    protected $secureDefault = false;

    /**
     * Get the value.
     * 
     * @var string $value
     */
    protected $value;

    /**
     * Constructor. Create a new Cookie class instance.
     * 
     * @param  string  $name  The name of the cookie
     * @param  string|null  $value  The value of the cookie
     * @param  int  $expire  The time the cookie expires
     * @param  string  $path  The path on the server in which the cookie will be available
     * @param  string|null  $domain  The domain that the cookie is available to
     * @param  bool|null  $secure  Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using HTTPS
     * @param  bool  $httpOnly  Whether the cookie will be made accessible only through the HTTP protocol
     * @param  bool  $raw  Whether the cookie value should be sent with no url encoding
     * @param  string|null  $sameSite  Whether the cookie will be available for cross-site requests
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $name,
        string $value = null,
        $expire = 0,
        ?string $path = '/',
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = self::SAMESITE_RESTRICTION_LAX
    ) {
        if ($raw && false !== strpbrk($name, self::SYS_RESERVED_CHARS_LIST)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters', $name)
            );
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty');
        }

        $this->name     = $name;
        $this->value    = $value;
        $this->expire   = static::expiresTimestamp($expire);
        $this->path     = empty($path) ? '/' : $path;
        $this->domain   = static::normalizeDomain($domain);
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw      = $raw;
        $this->sameSite = $this->withSameSite($sameSite)->sameSite;
    }
    
    /**
     * Creates a cookie copy with a new value.
     * 
     * @param  string  $value
     * 
     * @return self
     */
    public function withValue(?string $value): self
    {
        $cookie = clone $this;
        $cookie->value = $value;
        
        return $cookie;
    }
    
    /**
     * Creates a cookie copy with a new time the cookie expires.
     * 
     * @param  int  $expire
     * 
     * @return self
     */
    public function withExpires($expire = 0): self
    {
        $cookie = clone $this;
        $cookie->expire = self::expiresTimestamp($expire);

        return $cookie;
    }
    
    /**
     * Converts expires formats to a unix timestamp.
     * 
     * @param  int  $expire
     * 
     * @return int
     */
    private static function expiresTimestamp($expire = 0): int
    {
        // convert expiration time to a Unix timestamp
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif ( ! is_numeric($expire)) {
            $expire = strtotime($expire);
            
            if (false === $expire) {
                throw new InvalidArgumentException('The cookie expiration time is not valid');
            }
        }
        
        return 0 < $expire ? (int) $expire : 0;
    }
    
    /**
     * Creates a cookie copy with a new path on the server in which 
     * the cookie will be available on.
     * 
     * @param  string  $path
     * 
     * @return self
     */
    public function withPath(string $path): self
    {
        $cookie = clone $this;
        $cookie->path = '' === $path ? '/' : $path;
        
        return $cookie;
    }

    /**
     * Creates a cookie copy with a new domain that the cookie is available to.
     *
     * @param  string  $domain
     * 
     * @return self
     */
    public function withDomain(?string $domain): self
    {
        $cookie = clone $this;
        $cookie->domain = static::normalizeDomain($domain);
        
        return $cookie;
    }
    
    /**
     * Normalizes the domain URL.
     * 
     * @param  string|null  $domain
     * 
     * @return string
     */
    private static function normalizeDomain($domain = null)
    {
        // make sure that the domain is a string
        $domain = (string) $domain;
        
        // if the cookie should be valid for the current host only
        if ('' === $domain) {
            return null;
        }
        
        // if the provided domain is actually an IP address
        if (false !== filter_var($domain, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        // for local hostnames (which either have no dot at all or a leading dot only)
        if (strpos($domain, '.') === false || strrpos($domain, '.') === 0) {
            return null;
        }
        
        // unless the domain already starts with a dot
        if ($domain[0] !== '.') {
            $domain = '.' . $domain;
        }
        
        // return the normalized domain
        return $domain;
    }
        
    /**
     * Creates a cookie copy that only be transmitted over a secure
     * HTTPS connection from the client.
     * 
     * @param  bool  $secure
     * 
     * @return self
     */
    public function withSecure(bool $secure = true): self
    {
        $cookie = clone $this;
        $cookie->secure = $secure;
        
        return $cookie;
    }
    
    /**
     * Creates a cookie copy that be accessible only through the HTTP protocol.
     * 
     * @param  bool  $httpOnly
     * 
     * @return self
     */
    public function withHttpOnly(bool $httpOnly = true): self
    {
        $cookie = clone $this;
        $cookie->httpOnly = $httpOnly;
        
        return $cookie;
    }
    
    /**
     * Creates a cookie copy that uses no url encoding.
     * 
     * @param  bool  $raw
     * 
     * @return self
     */
    public function withRaw(bool $raw = true): self
    {
        if ($raw && false !== strpbrk($this->name, self::SYS_RESERVED_CHARS_LIST)) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters', $this->name)
            );
        }
        
        $cookie = clone $this;
        $cookie->raw = $raw;
        
        return $cookie;
    }

    /**
     * Creates a cookie copy with SameSite attribute.
     * 
     * @param  string  $sameSite
     * 
     * @return self
     */
    public function withSameSite(?string $sameSite): self
    {
        if ('' === $sameSite) {
            $sameSite = null;
        } elseif (null !== $sameSite) {
            $sameSite = strtolower($sameSite);
        }
        
        if ( ! in_array($sameSite, [
                self::SAMESITE_RESTRICTION_LAX, 
                self::SAMESITE_RESTRICTION_STRICT, 
                self::SAMESITE_RESTRICTION_NONE, 
                null
            ], true)
        ) {
            throw new InvalidArgumentException('The "sameSite" parameter value is not valid');
        }
        
        $cookie = clone $this;
        $cookie->sameSite = $sameSite;
        
        return $cookie;
    }

    /**
     * Gets the name of the cookie.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     * 
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Gets the time the cookie expires.
     * 
     * @return int
     */
    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    /**
     * Gets the maximum age of the cookie.
     * 
     * @return int
     */
    public function getMaxAge(): int
    {
        $maxAge = $this->expire - time();

        return 0 >= $maxAge ? 0 : $maxAge;
    }

    /**
     * Gets the path of the cookie.
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Gets the domain of the cookie.
     * 
     * @return string
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Checks whether the cookie should be sent over HTTPS only.
     * 
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure ?? $this->secureDefault;
    }

    /**
     * Checks whether the cookie should be accessible through HTTP only.
     * 
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }
    
    /**
     * Checks whether this cookie is about to be cleared.
     *
     * @return bool
     */
    public function isCleared(): bool
    {
        return 0 !== $this->expire && $this->expire < time();
    }
    
    /**
     * Checks if the cookie value should be sent with no url encoding.
     * 
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * Gets the same-site restriction of the cookie.
     * 
     * @return string
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }

    /**
     * Set the default value for the secure in the cookies.
     * 
     * @param  bool  $secure
     * 
     * @return void
     */
    public function setSecureDefault(bool $secure): void
    {
        $this->secureDefault = $secure;
    }
    
    /**
     * Magic method.
     * 
     * Returns the cookie as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->build(
                $this->getName(),
                $this->getValue(),
                $this->getExpiresTime(),
                $this->getPath(),
                $this->getDomain(),
                $this->isSecure(),
                $this->isHttpOnly(),
                $this->isRaw(),
                $this->getSameSite());
    }
}