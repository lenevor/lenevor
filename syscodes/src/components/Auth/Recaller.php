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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Auth;

/**
 * Allows has the recaller in a cookie string.
 */
class Recaller
{
    /**
     * The recaller or "remember me" cookie string.
     * 
     * @var string $recaller
     */
    protected $recaller;
    
    /**
     * Constructor. Create a new Recaller class instance.
     * 
     * @param  string  $recaller
     * 
     * @return void
     */
    public function __construct($recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
    }
    
    /**
     * Get the user ID from the recaller.
     * 
     * @return string
     */
    public function id(): string
    {
        return explode('|', $this->recaller, 3)[0];
    }
    
    /**
     * Get the "remember token" token from the recaller.
     * 
     * @return string
     */
    public function token(): string
    {
        return explode('|', $this->recaller, 3)[1];
    }
    
    /**
     * Get the password from the recaller.
     * 
     * @return string
     */
    public function hash(): string
    {
        return explode('|', $this->recaller, 4)[2];
    }
    
    /**
     * Determine if the recaller is valid.
     * 
     * @return bool
     */
    public function valid(): bool
    {
        return $this->properString() && $this->hasAllSegments();
    }
    
    /**
     * Determine if the recaller is an invalid string.
     * 
     * @return bool
     */
    protected function properString(): bool
    {
        return is_string($this->recaller) && str_contains($this->recaller, '|');
    }
    
    /**
     * Determine if the recaller has all segments.
     * 
     * @return bool
     */
    protected function hasAllSegments(): bool
    {
        $segments = explode('|', $this->recaller);
        
        return count($segments) >= 3 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }
    
    /**
     * Get the recaller's segments.
     * 
     * @return array
     */
    public function segments(): array
    {
        return explode('|', $this->recaller);
    }
}