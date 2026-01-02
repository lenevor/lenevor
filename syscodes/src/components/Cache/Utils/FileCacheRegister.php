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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Cache\Utils;

use Serializable;

/**
 * Class cache.
 * 
 * Allows to record data and its expiration time.
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
     * @param  string|null  $data
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
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Representation in chain format of an object.
     * 
     * @return mixed
     */
    public function serialize(): mixed
    {
        $data = (string) $this->data;

        return serialize($data);
    }
    
    /**
     * Constructs the object.
     * 
     * @param  string  $unserialize
     * 
     * @return static
     */
    public function unserialize($unserialize): static
    {
        $this->data = unserialize($unserialize);
        
        return $this;
    }
}