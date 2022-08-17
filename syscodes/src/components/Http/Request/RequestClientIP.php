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

namespace Syscodes\Components\Http\Request;

/**
 * This is a lightweight class for detecting client IP address:
 * - It uses specific HTTP headers to detect the real/original.
 * - Also, client IP address not final proxy IP.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class RequestClientIP
{
    /**
     * Get the client IP.
     * 
     * @var mixed $clientIp
     */
    protected $clientIp = null;

    /**
     * Get the client long IP.
     * 
     * @var mixed $clientLongIp
     */
    protected $clientLongIp = null;

    /**
     * Get the HTTP servers.
     * 
     * @var array $headers
     */
    protected $headers = [];

    /**
     * All possible HTTP headers for represent the 
     * IP address string.
     * 
     * @var array $ipServerHeaders
     */
    protected $ipServerHeaders = [
        'VIA',
        'X-REAL-IP',
        'REMOTE_ADDR',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED',
        'X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED_FOR',        
        'HTTP_X_CLUSTER_CLIENT_IP',         
    ];

    /**
     * Constructor. The create a new RequestClientIP class instance.
     * 
     * @param  array  $headers  Get headers from the request
     * 
     * @return void
     */
    public function __construct(array $headers = [])
    {
        $this->setServerHeader($headers);
        $this->setClientIp();
        $this->setClientLongIp($this->getclientIp());
    }

    /**
     * Set the SERVER Headers. This method set IP headers 
     * data with sent manually headers array.
     * 
     * @param  array  $headers
     * 
     * @return void
     */
    public function setServerHeader(array $headers = []): void
    {
        if ( ! is_array($headers) || ! count($headers)) {
            $headers = $_SERVER;
        }

        foreach ($this->getIpServerHeaders() as $key) {
            if (array_key_exists($key, $headers)) {
                $this->headers[$key] = $headers[$key];
            }
        }
    }

    /**
     * Get all possible SERVER headers that can contain 
     * the IP address.
     * 
     * @return array
     */
    protected function getIpServerHeaders(): array
    {
        return $this->ipServerHeaders;
    }

    /**
     * Retrieves the IP detect headers.
     * 
     * @return array
     */
    protected function getServerHeader(): array
    {
        return $this->headers;
    }

    /**
     * Return client IPv4.
     * 
     * @return mixed
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }
    
    /**
     * Set the real valid IP address from serverHeaders.
     * 
     * @return bool|string
     */
    protected function setClientIp()
    {
        foreach ($this->getIpServerHeaders() as $ipServerHeader) {
            if (isset($this->headers[$ipServerHeader])) {
                foreach (explode(',', $this->headers[$ipServerHeader]) as $ip) {
                    $ip = trim($ip);
                    
                    if ($this->validateIp($ip)) {
                        $this->clientIp = $ip;
                        
                        return $ip;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Return client LongIpv4.
     * 
     * @return mixed
     */
    public function getClientLongIp()
    {
        return $this->clientLongIp;
    }
    
    /**
     * Set the real valid long IP address.
     * 
     * @param  string|null  $ip  IPv4
     * 
     * @return bool|null
     */
    protected function SetClientLongIp($ip = null)
    {
        if ($this->validateIp($ip)) {
            // Fix bug to ip2long returning negative val
            $this->clientLongIp = sprintf('%u', ip2long($ip));
            
            return $this->clientLongIp;
        }
        
        return false;
    }
    
    /**
     * Ensures an ip address is both a valid IP.
     * 
     * @param  string  $ip  IPv4
     * 
     * @return bool
     */
    public function validateIp(?string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            return true;
        }
        
        return false;
    }
}