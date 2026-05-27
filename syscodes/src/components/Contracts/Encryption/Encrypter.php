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

namespace Syscodes\Components\Contracts\Encryption;

/**
 * Allows to encrypt and decrypt the values that are required as security
 * mesuares in the recommended variables towards the use of the browser 
 * or server.
 */
interface Encrypter
{
    /**
     * Encrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool  $serialize
     * 
     * @return string
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\EncryptException
     */
    public function encrypt(#[\SensitiveParameter] $value, $serialize = true): string;

    /**
     * Encrypt the given value.
     * 
     * @param  string  $value
     * @param  bool  $unserialize
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\DecryptException
     */
    public function decrypt($value, $unserialize = true);

    /**
     * Decrypt the given string without unserialization.
     * 
     * @param  string  $value
     * 
     * @return string
     * 
     * @throws \Syscodes\Components\Encryption\Exceptions\DecryptException
     */
    public function decryptString($value);

    /**
     * Gets the encryption key.
     * 
     * @return string
     */
    public function getKey(): string;

    /**
     * Get the current encryption key and all previous encryption keys.
     *
     * @return array
     */
    public function getAllKeys(): array;

    /**
     * Get the previous encryption keys.
     *
     * @return array
     */
    public function getPreviousKeys(): array;
}