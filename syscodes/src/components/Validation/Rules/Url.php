<?php

/*
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

use Syscodes\Components\Validation\Rules;
  
/**
 * Gets the attribute must be url's.
 */
class Url extends Rules
{
    /** 
     * The message depends of attribute.
     * 
     * @var string $message
     */
    protected $message = "The :attribute is not valid url";
    
    /**
     * Given $params and assign the params.
     * 
     * @param  array  $params
     * 
     * @return static
     */
    public function fillParameters(array $params): static
    {
        if (count($params) == 1 and is_array($params[0])) {
            $params = $params[0];
        }
        
        return $this->forScheme($params);
    }
    
    /**
     * Given schemes and assign the params.
     * 
     * @param  array  $schemes
     * 
     * @return static
     */
    public function forScheme($schemes): static
    {
        $this->params['schemes'] = (array) $schemes;
        
        return $this;
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
        $schemes = $this->parameter('schemes');
        
        if ( ! $schemes) {
            return $this->validateCommonScheme($value);
        } else {
            foreach ((array) $schemes as $scheme) {
                $method = 'validate' . ucfirst($scheme) .'Scheme';
                
                if (method_exists($this, $method)) {
                    if ($this->{$method}($value)) {
                        return true;
                    }
                } elseif ($this->validateCommonScheme($value, $scheme)) {
                    return true;
                }
            }
            
            return false;
        }
    }
    
    /**
     * Validate value is correct scheme format.
     * 
     * @param  mixed  $value
     * @param  null  $scheme
     * 
     * @return bool
     */
    public function validateCommonScheme($value, $scheme = null): bool
    {
        if ( ! $scheme) {
            return $this->validateBasic($value) && (bool) preg_match("/^\w+:\/\//i", $value);
        } else {
            return $this->validateBasic($value) && (bool) preg_match("/^{$scheme}:\/\//", $value);
        }
    }
    
    /**
     * Validate the value is mailto scheme format.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function validateMailtoScheme($value): bool
    {
        return $this->validateBasic($value) && preg_match("/^mailto:/", $value);
    }
    
    /**
     * Validate the value is jdbc scheme format.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function validateJdbcScheme($value): bool
    {
        return (bool) preg_match("/^jdbc:\w+:\/\//", $value);
    }
    
    /**
     * Validate $value is valid URL format.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function validateBasic($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}