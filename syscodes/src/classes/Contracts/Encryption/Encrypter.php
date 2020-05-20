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
 * @since       0.5.0
 */

namespace Syscodes\Contracts\Encryption;

/**
 * Allows to encrypt and decrypt the values that are required as security
 * mesuares in the recommended variables towards the use of the browser 
 * or server.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
interface Encrypter
{
    /**
     * Determine if the given key and cipher combination is valid.
     * 
     * @param  string  $key
     * @param  string  $cipher
     * 
     * @return bool
     */
    public static function supported($key, $cipher);

    /**
     * Generate the IV size for the cipher.
     * 
     * @param  string  $cipher
     * 
     * @return string
     */
    public static function generateRandomKey($cipher);

    /**
     * Encrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool  $serialize  (true by defect) 
     * 
     * @return string
     * 
     * @throws \Syscodes\Encryption\Exceptions\EncryptException
     */
    public function encrypt($value, $serialize = true);

    /**
     * Encrypt the given string without serialization.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public function encryptString($value);

    /**
     * Encrypt the given value.
     * 
     * @param  string  $value
     * @param  bool  $unserialize  (true by defect) 
     * 
     * @return mixed
     * 
     * @throws \Syscodes\Encryption\Exceptions\DecryptException
     */
    public function decrypt($value, $unserialize = true);

    /**
     * Decrypt the given string without unserialization.
     * 
     * @param  string  $value
     * 
     * @return string
     * 
     * @throws \Syscodes\Encryption\Enxceptions\DecryptException
     */
    public function decryptString($value);

    /**
     * Gets the encryption key.
     * 
     * @return string
     */
    public function getKey();
}