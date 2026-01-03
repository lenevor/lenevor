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

namespace Syscodes\Components\Hashing\Drivers;

use RuntimeException;

/**
 * This class allows the check and verification of the hash 
 * given value with Argon2id.
 */
class Argon2IdHasher extends ArgonHasher
{
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
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'argon2id') {
            throw new RuntimeException('This password does not use the Argon2id algorithm');
        }
        
        if (is_null($hashedValue) || 0 === strlen($hashedValue)) {
            return false;
        }
        
        return password_verify($value, $hashedValue);
    }

    /**
     * Get the algorithm that should be used for hashing.
     * 
     * @return int|string
     */
    protected function algorithm(): int|string
    {
        return PASSWORD_ARGON2ID;
    }
}