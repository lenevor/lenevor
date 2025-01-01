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

namespace Syscodes\Components\Routing\Concerns;

/**
 * Describe the parameters of the routes according to the user condition 
 * the action that corresponds to each route as: the host, the scheme 
 * and the port.
 */
trait RouteCondition
{
    /**
     * Gets the host.
     * 
     * @var string $host
     */
    protected $host;

    /**
     * Gets the port.
     * 
     * @var int $port
     */
    protected $port;

    /**
     * Gets the scheme.
     * 
     * @var string $scheme
     */
    protected $scheme;

    /**
     * Gets the host from a route.
     * 
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the host from a route chosen by the user.
     * 
     * @param  string  $host
     * 
     * @return static
     */
    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Alias to call the host name from a route.
     * 
     * @param  string  $host
     * 
     * @return static
     */
    public function host(string $host): static
    {
        return $this->setHost($host);
    }

    /**
     * Gets the port from a route.
     * 
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the port from a route chosen by the user.
     * 
     * @param  int  $port
     * 
     * @return static
     */
    public function setPort(int $port): static
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Alias to call the port from a route.
     * 
     * @param  int  $port
     * 
     * @return static
     */
    public function port(int $port): static
    {
        return $this->setPort($port);
    }

    /**
     * Gets the scheme from a route.
     * 
     * @return string|null
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the scheme from a route chosen by the user.
     * 
     * @param  string  $scheme
     * 
     * @return static
     */
    public function setScheme(string $scheme): static
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Alias to call the scheme from a route.
     * 
     * @param  string  $scheme
     * 
     * @return static
     */
    public function scheme(string $scheme): static
    {
        return $this->setScheme($scheme);
    }
}