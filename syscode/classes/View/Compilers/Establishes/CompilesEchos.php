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
 * @author      Javier Alexander Campo M. <jalexcam@gmail.com>
 * @link        https://lenevor.com 
 * @copyright   Copyright (c) 2019-2020 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.6.0
 */

namespace Syscode\View\Compilers\Establishes;

/**
 * Trait CompilesIncludes.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait CompilesEchos
{
    /**
     * Compile Plaze echos into valid PHP.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    public function compileEchos($value)
    {
        foreach ($this->getEchoMethods() as $method)
        {
            $value = $this->$method($value);
        }
        
        return $value;
    }
    
    /**
     * Get the echo methods in the proper order for compilation.
     * 
     * @return array
     */
    protected function getEchoMethods()
    {
        return [
            'compileRawEchos',
            'compileEscapedEchos',
            'compileRegularEchos',
        ];
    }
    
    /**
     * Compile the "raw" echo statements.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);
        
        $callback = function ($matches)
        {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$matches[2]}; ?>{$whitespace}";
        };
        
        return preg_replace_callback($pattern, $callback, $value);
    }
    
    /**
     * Compile the "regular" echo statements.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);
        
        $callback = function ($matches)
        {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            
            $wrapped    = sprintf($this->echoFormat, $matches[2]);
            
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$wrapped}; ?>{$whitespace}";
        };

        return preg_replace_callback($pattern, $callback, $value);
    }
    
    /**
     * Compile the escaped echo statements.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);
        
        $callback = function ($matches)
        {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            
            return $matches[1] ? $matches[0] : "<?php echo e({$matches[2]}); ?>{$whitespace}";
        };
        
        return preg_replace_callback($pattern, $callback, $value);
    }
}