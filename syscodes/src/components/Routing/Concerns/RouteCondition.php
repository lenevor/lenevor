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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.5.0
 */

namespace Syscodes\Routing\Concerns;

/**
 * Describe the parameters of the routes according to the user condition 
 * the action that corresponds to each route as: the host, the scheme 
 * and the port.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
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
     * @return void
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
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Alias to call the host name from a route.
     * 
     * @param  string  $host
     * 
     * @return $this
     */
    public function host(string $host)
    {
        return $this->setHost($host);
    }

    /**
     * Gets the port from a route.
     * 
     * @return void
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
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Alias to call the port from a route.
     * 
     * @param  int  $port
     * 
     * @return $this
     */
    public function port(int $port)
    {
        return $this->setPort($port);
    }

    /**
     * Gets the scheme from a route.
     * 
     * @return void
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
     * @return $this
     */
    public function setScheme(string $scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Alias to call the scheme from a route.
     * 
     * @param  string  $scheme
     * 
     * @return $this
     */
    public function scheme(string $scheme)
    {
        return $this->setScheme($scheme);
    }
}