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

namespace Syscodes\Components\Validation\Rules\Traits;

use Syscodes\Components\Support\Arr;

/**
 * Check files.
 */
trait File
{
    /**
     * Check whether value is from $_FILES.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function isValueFromUploadedFiles($value): bool
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
    
    /**
     * Check the $value is uploaded file.
     * 
     * @param  mixed  $value
     * 
     * @return bool
     */
    public function isUploadedFile($value): bool
    {
        return $this->isValueFromUploadedFiles($value) && is_uploaded_file($value['tmp_name']);
    }
    
    /**
     * Resolve uploaded file value.
     * 
     * @param  mixed  $value
     * 
     * @return array|null
     */
    public function resolveUploadedFileValue($value): array|null
    {
        if ( ! $this->isValueFromUploadedFiles($value)) {
            return null;
        }
        
        $arrayDots = Arr::dot($value);
        
        $results   = [];
        
        foreach ($arrayDots as $key => $val) {
            $splits   = explode(".", $key);
            $firstKey = array_shift($splits);
            
            $key = count($splits) ? implode(".", $splits) . ".{$firstKey}" : $firstKey;
            
            Arr::set($results, $key, $val);
        }
        
        return $results;
    }
}