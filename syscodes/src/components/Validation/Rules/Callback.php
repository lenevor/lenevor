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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Validation\Rules;

use Closure;
use InvalidArgumentException;
use Syscodes\Components\Validation\Rules;

/**
 * Gets the attribute must be callbak.
 */
class Callback extends Rules
{
    /** 
     * The message depends of attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute is not valid";
    
    /**
     * The fillable params.
     * 
     * @var array $fillableParams
     */
    protected $fillableParams = ['callback'];
    
    /**
     * Set the Callback closure.
     * 
     * @param Closure $callback
     * 
     * @return static
     */
    public function setCallback(Closure $callback): static
    {
        return $this->setParameter('callback', $callback);
    }

    /**
     * Check the value is valid.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function check($value): bool
    {
        $this->requireParameters($this->fillableParams);
        
        $callback = $this->parameter('callback');
        
        if (false === $callback instanceof Closure) {
            $key = $this->attribute->getKey();
            
            throw new InvalidArgumentException("Callback rule for '{$key}' is not callable");
        }
        
        $callback       = $callback->bindTo($this);
        $invalidMessage = $callback($value);
        
        if (is_string($invalidMessage)) {
            $this->setMessage($invalidMessage);
            
            return false;
        } elseif (false === $invalidMessage) {
            return false;
        }
        
        return true;
    }
}