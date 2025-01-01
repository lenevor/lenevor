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

namespace Syscodes\Components\Http\Helpers;

/**
 * Allows to use static methods referring normalized HTTP requests.
 */
class RequestUtils
{
    /**
     * Parse a string, but preserves dots in variable names.
     * 
     * @param  string  $query
     * @param  bool  $ignoreBrackets
     * @param  string  $separator
     * 
     * @return array
     */
    public static function parseQuery(string $query, bool $ignoreBrackets = false, string $separator = '&'): array
    {
        $q = [];
        
        foreach (explode($separator, $query) as $value) {
            if (false !== $i = strpos($value, "\0")) {
                $value = substr($value, 0, $i);
            }
            
            if (false === $i = strpos($value, '=')) {
                $k     = urldecode($value);                
                $value = '';
            } else {
                $k     = urldecode(substr($value, 0, $i));
                $value = substr($value, $i);
            }
            
            if (false !== $i = strpos($k, "\0")) {
                $k = substr($k, 0, $i);
            }
            
            $k = ltrim($k, ' ');
            
            if ($ignoreBrackets) {
                $q[$k][] = urldecode(substr($value, 1));
                
                continue;
            }
            
            if (false === $i = strpos($k, '[')) {
                $q[] = bin2hex($k).$value;
            } else {
                $q[] = bin2hex(substr($k, 0, $i)).rawurlencode(substr($k, $i)).$value;
            }
        }
        
        if ($ignoreBrackets) {
            return $q;
        }
        
        parse_str(implode('&', $q), $q);
        
        $query = [];
        
        foreach ($q as $k => $value) {
            if (false !== $i = strpos($k, '_')) {
                $query[substr_replace($k, hex2bin(substr($k, 0, $i)).'[', 0, 1 + $i)] = $value;
            } else {
                $query[hex2bin($k)] = $value;
            }
        }
        
        return $query;
    }
}