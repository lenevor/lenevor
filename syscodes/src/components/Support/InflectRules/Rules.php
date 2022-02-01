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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Support\InflectRules;

use Closure;

/**
 * Allows use rules for identify the words in plural or singular.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Rules
{
    /**
     * Get the words that should uncountables.
     * 
     * @var array $uncountables
     */
    protected array $uncountables;

    /**
     * Constructor. Create a new Rules class instance.
     * 
     * @param  array  $uncountables
     * 
     * @return void
     */
    public function __construct(array $uncountables = [])
    {
        $this->uncountables = $uncountables;
    }

    /**
     * Added the words uncountables.
     * 
     * @param  string  $word
     * 
     * @return void
     */
    public function addUncountable(string $word): void
    {
        $this->uncountables = $word;
    }
    
    /**
     * Replace the words with your respective rules.
     * 
     * @param  array  $replaceMap
     * @param  array  $keepMap
     * @param  array  $rules
     * 
     * @return \Closure
     */
    public function replace($replaceMap, $keepMap, $rules): Closure
    {
        return function ($word) use ($replaceMap, $keepMap, $rules) {
            $token = strtolower($word);
            
            if (array_key_exists($token, $keepMap)) {
                return $this->restore($word, $token);
            }
            
            if (array_key_exists($token, $replaceMap)) {
                return $this->restore($word, $replaceMap[$token]);
            }
            
            return $this->sanitizeWord($token, $word, $rules);
        };
    }
    
    /**
     * Check if a word is part of the map.
     * 
     * @param  array  $replaceMap
     * @param  array  $keepMap
     * @param  array  $rules
     * 
     * @return \Closure
     */
    public function checkWord($replaceMap, $keepMap, $rules): Closure
    {
        return function ($word) use ($replaceMap, $keepMap, $rules) {
            $token = strtolower($word);
            
            if (array_key_exists($token, $keepMap)) {
                return true;
            }
            
            if (array_key_exists($token, $replaceMap)) {
                return false;
            }
            
            return $this->sanitizeWord($token, $word, $rules) === $token;
        };
    }
    
    /**
     * Replace the words with your respective rules.
     * 
     * @param  string  $word
     * @param  array  $rule
     * 
     * @return  string
     */
    protected function replaceWord($word, $rule): string
    {
        return preg_replace_callback($rule[0], function ($matches) use ($word, $rule) {
            if ( ! isset($matches[0])) {
                return $word;
            }
            
            $result = $this->interpolate($rule[1], $matches);
            
            if ($matches[0] === '' && isset($matches[1])) {
                $sub = substr($word, $matches[1] - 1);
                
                return $this->restore($sub, $result);
            }
            return $this->restore($matches[0], $result);
        }, $word);
    }
    
    /**
     * Get the arguments based in regex depending of a string.
     * 
     * @param  string  $str
     * @param  array  $args
     * 
     * @return string
     */
    protected function interpolate($str, $args)
    {
        return preg_replace_callback('/\$(\d{1,2})/', function ($matches) use ($args) {
            return isset($matches[1], $args[$matches[1]])
                        ? $args[$matches[1]]
                        : "";
        }, $str);
    }
    
    /**
     * Get a string with the uppercase, lowercase and first-capital functions.
     * 
     * @param  string  $word
     * @param  string  $token
     * 
     * @return string
     */
    protected function restore($word, $token): string
    {
        if ($word === $token) {
            return $token;
        }
        
        if ($word === strtolower($word)) {
            return strtolower($token);
        }
        
        if ($word === strtoupper($word)) {
            return strtoupper($token);
        }
        
        if ($word === ucfirst($word)) {
            return ucfirst($token);
        }
        
        return strtolower($token);
    }
    
    /**
     * Sanitize a string using a rule words.
     * 
     * @param  string  $token
     * @param  string  $word
     * @param  array  $rules
     * 
     * @return string
     */
    private function sanitizeWord($token, $word, $rules)
    {
        if (empty($token) || array_key_exists($token, $this->uncountables)) {
            return $word;
        }
        
        $length = count($rules);
        
        while ($length--) {
            $rule = $rules[$length];
            
            if (preg_match($rule[0], $word)) {
                return $this->replaceWord($word, $rule);
            }
        }
        
        return $word;
    }
}