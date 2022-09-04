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

namespace Syscodes\Components\Cookie\Middleware;

use Syscodes\Components\Contracts\Encryption\Encrypter as EncryptContract;

/**
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class EncryptCookies
{
    /**
     * The Encrypter instance.
     * 
     * @var \Syscodes\Components\Contracts\Encryption\Encrypter $encrypter
     */
    protected $encrypter;

    /**
     * The names of the cookies that should not be encrypted.
     * 
     * @var array $except
     */
    protected $except = [];

    /**
     * Indicates if cookies should be serialized.
     * 
     * @var bool $serialized
     */
    protected $serialized = false;

    /**
     * Constructor. Create a new EncryptCookies class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Encryption\Encrypter  $encrypter
     * 
     * @return void
     */
    public function __construct(EncryptContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Disable encryption for the given cookie name(s).
     * 
     * @param  string  $name
     * 
     * @return void
     */
    public function disableFor($name): void
    {
        $this->except = array_merge($this->except, (array) $name);
    }
}