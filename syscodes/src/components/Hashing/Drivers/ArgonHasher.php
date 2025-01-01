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

use RuntimeException;
use Syscodes\Components\Contracts\Hashing\Hasher;

/**
 * This class allows the check and verification of the hash 
 * given value with Argon2i.
 */
class ArgonHasher extends AbstractHasher implements Hasher
{
    /**
     * The default memory cost factor.
     * 
     * @var int $memory
     */
    protected $memory = 1024;
    
    /**
     * The default time cost factor.
     * 
     * @var int $time
     */
    protected $time = 2;
    
    /**
     * The default threads factor.
     * 
     * @var int $threads
     */
    protected $threads = 2;
    
    /**
     * Indicates whether to perform an algorithm check.
     * 
     * @var bool $verifyAlgorithm
     */
    protected $verifyAlgorithm = false;

    /**
     * Constructor. Create a new ArgonHasher class instance.
     * 
     * @param  array  $options
     * 
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->time            = $this->time($options);
        $this->memory          = $this->memory($options);
        $this->threads         = $this->threads($options);
        $this->verifyAlgorithm = $options['verify'] ?? $this->verifyAlgorithm;    
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
        $hash = @password_hash($value, $this->algorithm(), [
                    'memory_cost' => $this->memory($options),
                    'time_cost' => $this->time($options),
                    'threads' => $this->threads($options),
                ]);
        
        if ( ! is_string($hash)) {
            throw new RuntimeException('Argon2 hashing not supported');
        }
        
        return $hash;
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
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'argon2i') {
            throw new RuntimeException('This password does not use the Argon2i algorithm');
        }
        
        return parent::check($value, $hashedValue, $options);
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
        return password_needs_rehash($hashedValue, $this->algorithm(), [
                    'memory_cost' => $this->memory($options),
                    'time_cost' => $this->time($options),
                    'threads' => $this->threads($options),
               ]);
    }
    
    /**
     * Set the default password memory factor.
     * 
     * @param  int  $memory
     * 
     * @return static
     */
    public function setMemory(int $memory): static
    {
        $this->memory = $memory;
        
        return $this;
    }
    
    /**
     * Set the default password timing factor.
     * 
     * @param  int  $time
     * 
     * @return static
     */
    public function setTime(int $time): static
    {
        $this->time = $time;
        
        return $this;
    }
    
    /**
     * Set the default password threads factor.
     * 
     * @param  int  $threads
     * 
     * @return static
     */
    public function setThreads(int $threads): static
    {
        $this->threads = $threads;
        
        return $this;
    }

    /**
     * Get the algorithm that should be used for hashing.
     * 
     * @return int|string
     */
    protected function algorithm(): int|string
    {
        return PASSWORD_ARGON2I;
    }
    
    /**
     * Extract the time cost value from the options array.
     * 
     * @param  array  $options
     * 
     * @return int
     */
    protected function time(array $options): int
    {
        return $options['time'] ?? $this->time;
    }
    
    /**
     * Extract the memory cost value from the options array.
     * 
     * @param  array  $options
     * 
     * @return int
     */
    protected function memory(array $options): int
    {
        return $options['memory'] ?? $this->memory;
    }
    
    /**
     * Extract the thread's value from the options array.
     * 
     * @param  array  $options
     * 
     * @return int
     */
    protected function threads(array $options): int
    {
        if (defined('PASSWORD_ARGON2_PROVIDER') && PASSWORD_ARGON2_PROVIDER === 'sodium') {
            return 1;
        }
        
        return $options['threads'] ?? $this->threads;
    }
}