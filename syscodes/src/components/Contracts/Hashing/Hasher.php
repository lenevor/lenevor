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

namespace Syscodes\Components\Contracts\Hashing;

/**
 * Verify if exists the hash given value.
 */
interface Hasher
{
    /**
     * Get information about the given hashed value.
     * 
     * @param  string  $hashedValue
     * 
     * @return array
     */
    public function info($hashedValue): array;

    /**
     * Hash the given value.
     * 
     * @param  string  $value
     * @param  array   $options
     * 
     * @return string
     */
    public function make($value, array $options = []): string;
    
    /**
     * Check the given plain value against a hash.
     * 
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array   $options
     * 
     * @return bool
     */
    public function check($value, $hashedValue, array $options = []): bool;
    
    /**
     * Check if the given hash has been hashed using the given options.
     * 
     * @param  string  $hashedValue
     * @param  array   $options
     * 
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = []): bool;
}