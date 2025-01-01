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

namespace Syscodes\Components\Hashing\Drivers;

/**
 * Allows get the information and verifiy of the given hashed value.
 */
abstract class AbstractHasher
{
    /**
     * Get information about the given hashed value.
     * 
     * @param  string  $hashedValue
     * 
     * @return array
     */
    public function info($hashedValue): array
    {
        return password_get_info($hashedValue);
    }

    /**
     * Check the given plain value against a hash.
     * 
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array   $options
     * 
     * @return bool
     */
    public function check($value, $hashedValue, array $options = []): bool
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }
        
        return password_verify($value, $hashedValue);
    }
}