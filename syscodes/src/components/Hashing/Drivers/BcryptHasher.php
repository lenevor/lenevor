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
use Syscodes\Components\Contracts\Hashing\Hasher;

/**
 * This class allows the check and verification of the hash 
 * given value with Bcrypt.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class BcryptHasher extends AbstractHasher implements Hasher
{
    /**
     * The default cost factor.
     * 
     * @var int $rounds
     */
    protected $rounds = 10;
    
    /**
     * Indicates whether to perform an algorithm check.
     * 
     * @var bool $veryAlgoritm
     */
    protected $verifyAlgorithm = false;
    
    /**
     * Constructor. Create a new hasher instance.
     * 
     * @param  array  $options
     * 
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->rounds          = $options['rounds'] ?? $this->rounds;
        $this->verifyAlgorithm = $options['verify'] ?? $this->verifyAlgorithm;
    }
    
    /**
     * Hash the given value.
     * 
     * @param  string  $value
     * @param  array  $options
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    public function make($value, array $options = []): string
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
        
        if ($hash === false) {
            throw new RuntimeException('Bcrypt hashing not supported');
        }
        
        return $hash;
    }
    
    /**
     * Check the given plain value against a hash.
     * 
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * 
     * @return bool
     * 
     * @throws \RuntimeException
     */
    public function check($value, $hashedValue, array $options = []): bool
    {
        if ($this->verifyAlgorithm && $this->info($hashedValue)['algoName'] !== 'bcrypt') {
            throw new RuntimeException('This password does not use the Bcrypt algorithm');
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
        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);
    }
    
    /**
     * Set the default password work factor.
     * 
     * @param  int  $rounds
     * 
     * @return static
     */
    public function setRounds($rounds): static
    {
        $this->rounds = (int) $rounds;
        
        return $this;
    }
    
    /**
     * Extract the cost value from the options array.
     * 
     * @param  array  $options
     * 
     * @return int
     */
    protected function cost(array $options = []): int
    {
        return $options['rounds'] ?? $this->rounds;
    }
}