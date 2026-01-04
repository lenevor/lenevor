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

namespace Syscodes\Components\View\Transpilers\Concerns;

/**
 * Trait CompilesEchos.
 */
trait TranspilesEchos
{
    /**
     * Transpile Plaze echos into valid PHP.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileEchos($value): string
    {
        foreach ($this->getEchoMethods() as $method)
        {
            $value = $this->$method($value);
        }
        
        return $value;
    }
    
    /**
     * Get the echo methods in the proper order for transpilation.
     * 
     * @return array
     */
    protected function getEchoMethods(): array
    {
        return [
            'transpileRawEchos',
            'transpileEscapedEchos',
            'transpileRegularEchos',
        ];
    }
    
    /**
     * Transpile the "raw" echo statements.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileRawEchos($value): string
    {
        $pattern = sprintf('/(<@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);
        
        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$matches[2]}; ?>{$whitespace}";
        };
        
        return preg_replace_callback($pattern, $callback, $value);
    }
    
    /**
     * Transpile the "regular" echo statements.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileRegularEchos($value): string
    {
        $pattern = sprintf('/(<@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);
        
        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            
            $wrapped    = sprintf($this->echoFormat, $matches[2]);
            
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$wrapped}; ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
    
    /**
     * Transpile the escaped echo statements.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileEscapedEchos($value): string
    {
        $pattern = sprintf('/(<@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);
        
        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            
            return $matches[1] ? $matches[0] : "<?php echo e({$matches[2]}); ?>{$whitespace}";
        };
        
        return preg_replace_callback($pattern, $callback, $value);
    }
}