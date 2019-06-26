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
 * @copyright   Copyright (c) 2019 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscode\Cache\Drivers\Utils;

use Serializable;

/**
 * Class cache.
 * 
 * Allows to record data and its expiration time.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class FileCacheRegister implements Serializable
{
    /**
     * The data to be stored.
     * 
     * @var string $data
     */
    protected $data;

    /**
     * Time expiration in to be stored.
     * 
     * @var int $expiration
     */
    protected $expiration;

    /**
     * Constructor class.
     * 
     * @param  string  $data
     * @param  int     $expiration
     * 
     * @return string
     */
    public function __construct($data, $expiration)
    {
        $this->data       = $data;
        $this->expiration = $expiration;
    }

    /**
     * Get the data.
     * 
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the expiration.
     * 
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Representation in chain format of an object.
     * 
     * @return mixed
     */
    public function serialize()
    {
        return serialize([$this->data, $this->expiration]);
    }
    
    /**
     * Constructs the object.
     * 
     * @param  string  $serialized
     * 
     * @return string
     */
    public function unserialize($serialized)
    {
        $unserialize = unserialize($serialized);
        $this->data       = $unserialize[0];
        $this->expiration = $unserialize[1];
    }
}