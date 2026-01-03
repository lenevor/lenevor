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

namespace Syscodes\Components\Hashing;

use Syscodes\Components\Contracts\Hashing\Hasher;
use Syscodes\Components\Hashing\Drivers\Argon2IdHasher;
use Syscodes\Components\Hashing\Drivers\ArgonHasher;
use Syscodes\Components\Hashing\Drivers\BcryptHasher;
use Syscodes\Components\Support\Manager;

/**
 * The Lenevor hash system for encrypted.
 */
class HashManager extends Manager implements Hasher
{
    /**
     * Create an instance of the Bcrypt hash Driver.
     * 
     * @return \Syscodes\Components\Hashing\Drivers\BcryptHasher
     */
    public function createBcryptDriver()
    {
        return new BcryptHasher($this->config->get('hashing.bcrypt') ?? []);
    }
    
    /**
     * Create an instance of the Argon2i hash Driver.
     * 
     * @return \Syscodes\Components\Hashing\ArgonHasher
     */
    public function createArgonDriver()
    {
        return new ArgonHasher($this->config->get('hashing.argon') ?? []);
    }
    
    /**
     * Create an instance of the Argon2id hash Driver.
     * 
     * @return \Syscodes\Components\Hashing\Argon2IdHasher
     */
    public function createArgon2idDriver()
    {
        return new Argon2IdHasher($this->config->get('hashing.argon') ?? []);
    }
    
    /**
     * Get information about the given hashed value.
     * 
     * @param  string  $hashedValue
     * 
     * @return array
     */
    public function info($hashedValue): array
    {
        return $this->driver()->info($hashedValue);
    }
    
    /**
     * Hash the given value.
     * 
     * @param  string  $value
     * @param  array   $options
     * 
     * @return string
     */
    public function make($value, array $options = []): string
    {
        return $this->driver()->make($value, $options);
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
        return $this->driver()->check($value, $hashedValue, $options);
    }
    
    /**
     * Check if the given hash has been hashed using the given options.
     * 
     * @param  string  $hashedValue
     * @param  array   $options
     * 
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = []): bool
    {
        return $this->driver()->needsRehash($hashedValue, $options);
    }
    
    /**
     * Determine if a given string is already hashed.
     * 
     * @param  string  $value
     * 
     * @return bool
     */
    public function isHashed($value): bool
    {
        return password_get_info($value)['algo'] !== null;
    }
    
    /**
     * Get the default driver name.
     * 
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('hashing.driver', 'bcrypt');
    }
}