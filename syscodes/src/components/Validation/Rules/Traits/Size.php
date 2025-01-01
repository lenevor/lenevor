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

namespace Syscodes\Components\Validation\Rules\Traits;

use InvalidArgumentException;

/**
 * Gets the value of a given size.
 */
trait Size
{
    /**
     * Get size (int) value from given value.
     * 
     * @param  int|string  $value
     * 
     * @return float|false
     */
    protected function getValueSize($value): float|false
    {
        if ($this->getAttribute() && 
           ($this->getAttribute()->hasRule('numeric') || 
           $this->getAttribute()->hasRule('integer')) &&
           is_numeric($value)
        ) {
            $value = (float) $value;
        }
        
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        } elseif (is_string($value)) {
            return (float) mb_strlen($value, 'UTF-8');
        } elseif ($this->isUploadedFileValue($value)) {
            return (float) $value['size'];
        } elseif (is_array($value)) {
            return (float) count($value);
        } else {
            return false;
        }
    }
    
    /**
     * Given $size and get the bytes.
     * 
     * @param  string|int  $size
     * 
     * @return float
     * 
     * @throws InvalidArgumentException
     */
    protected function getBytesSize($size): float
    {
        if (is_numeric($size)) {
            return (float) $size;
        }
        
        if ( ! is_string($size)) {
            throw new InvalidArgumentException("Size must be string or numeric Bytes", 1);
        }
        
        if ( ! preg_match("/^(?<number>((\d+)?\.)?\d+)(?<format>(B|K|M|G|T|P)B?)?$/i", $size, $match)) {
            throw new InvalidArgumentException("Size is not valid format", 1);
        }
        
        $number = (float) $match['number'];
        $format = isset($match['format']) ? $match['format'] : '';
        
        return match (strtoupper($format)) {
            'KB', 'K' => $number * 1024,            
            'MB', 'M' => $number * pow(1024, 2),            
            'GB', 'G' => $number * pow(1024, 3),                
            'TB', 'T' => $number * pow(1024, 4),
            'PB', 'P' => $number * pow(1024, 5),                
            default => $number,
        };
    }
    
    /**
     * Check whether value is from $_FILES.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function isUploadedFileValue($value): bool
    {
        if ( ! is_array($value)) {
            return false;
        }
        
        $keys = ['name', 'type', 'tmp_name', 'size', 'error'];
        
        foreach ($keys as $key) {
            if ( ! array_key_exists($key, $value)) {
                return false;
            }
        }
        
        return true;
    }
}