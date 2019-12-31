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
 * @since       0.5.0
 */

namespace Syscode\Contracts\Encryption;

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
     * Encrypt the given value.
     * 
     * @param  mixed  $value
     * @param  bool   $serialize  (true by defect) 
     * 
     * @return string
     * 
     * @throws \Syscode\Encryption\Exceptions\EncryptException
     */
    public function encrypt($value, $serialize = true);

    /**
     * Encrypt the given value.
     * 
     * @param  string  $value
     * @param  bool    $unserialize  (true by defect) 
     * 
     * @return mixed
     * 
     * @throws \Syscode\Encryption\Exceptions\DecryptException
     */
    public function decrypt($value, $unserialize = true);
}