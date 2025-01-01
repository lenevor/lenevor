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

namespace Syscodes\Components\Support\Facades;

/**
 * Initialize the Encrypter class facade.
 * 
 * @method static bool supported(string $key, string $cipher)
 * @method static string generateRandomKey(string $cipher)
 * @method static string encrypt(mixed $value, bool $serialize = true)
 * @method static string encryptString(string $value)
 * @method static mixed decrypt(mixed $value, bool $unserialize = true)
 * @method static string decryptString(string $value)
 * @method static string getKey()
 * 
 * @see \Syscodes\Components\Encryption\Encrypter
 */
class Crypt extends Facade
{
    /**
     * Get the registered name of the component.
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return 'encrypter';
    }
}