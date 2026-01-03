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

namespace Syscodes\Components\Finder\Filters;

use Iterator;
use FilterIterator;

/**
 * Allows the filters files using patterns.
 */
abstract class MultiFilterIterator extends FilterIterator
{
    /**
     * Get the match regex.
     * 
     * @var array $matchRegex
     */
    protected $matchRegex = [];
    
    /**
     * Get the not match regex.
     * 
     * @var array $noMatchRegex
     */
    protected $noMatchRegex = [];
    
    /**
     * Constructor. Create a new MultiFilterIterator instance.
     * 
     * @param \Iterator<TKey, TValue> $iterator      The Iterator to filter
     * @param string[]                $matchRegex    An array of regex that need to match
     * @param string[]                $noMatchRegex  An array of regex that need to not match
     * 
     * @return void 
     */
    public function __construct(Iterator $iterator, array $matchRegex, array $noMatchRegex)
    {
        foreach ($matchRegex as $pattern) {
            $this->matchRegex[] = $this->toRegex($pattern);
        }
        
        foreach ($noMatchRegex as $pattern) {
            $this->noMatchRegex[] = $this->toRegex($pattern);
        }
        
        parent::__construct($iterator);
    }
    
    /**
     * Checks whether the string is accepted by the regex filters.
     * 
     * @param  string  $string
     * 
     * @return bool
     */
    protected function isAccepted(string $string): bool
    {
        // should at least not match one rule to exclude
        foreach ($this->noMatchRegex as $regex) {
            if (preg_match($regex, $string)) {
                return false;
            }
        }

        // should at least match one rule
        if ($this->matchRegex) {
            foreach ($this->matchRegex as $regex) {
                if (preg_match($regex, $string)) {
                    return true;
                }
            }

            return false;
        }
        
        // If there is no match rules, the file is accepted
        return true;
    }
    
    /**
     * Checks whether the string is a regex.
     * 
     * @param  string  $value
     */
    protected function isRegex(string $value): bool
    {
        $availableModifiers = 'imsxuADU';
        
        if (preg_match('/^(.{3,}?)['.$availableModifiers.']*$/', $value, $m)) {
            $start = substr($m[1], 0, 1);
            $end   = substr($m[1], -1);
            
            if ($start === $end) {
                return ! preg_match('/[*?[:alnum:] \\\\]/', $start);
            }
            
            foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
                if ($start === $delimiters[0] && $end === $delimiters[1]) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Converts string into regexp.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    abstract protected function toRegex(string $value): string;
}