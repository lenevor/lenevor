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
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.1.0
 */

namespace Syscodes\Cache\Utils;

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
     * Constructor class.
     * 
     * @param  string  $data  (null by default)
     * 
     * @return string
     */
    public function __construct($data = null)
    {
        $this->data = $data;
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
     * Representation in chain format of an object.
     * 
     * @return mixed
     */
    public function serialize()
    {
        return serialize($this->data);
    }
    
    /**
     * Constructs the object.
     * 
     * @param  string  $unserialize
     * 
     * @return $this
     */
    public function unserialize($unserialize)
    {
        $this->data = unserialize($unserialize);
        
        return $this;
    }
}