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
 * Trait CompilesLoops.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait CompilesLoops
{
    /**
     * Counter to keep track of nested forelse statements.
     * 
     * @var int $forElseCounter
     */
    protected $forElseCounter = 0;

    /**
     * Compile the for statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Compile the end-if statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndFor()
    {
        return '<?php endfor; ?>';
    }

    /**
     * Compile the foreach statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileForeach($expression)
    {
        return "<?php foreach{$expression}: ?>";
    }

    /**
     * Compile the end-foreach statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndForeach()
    {
        return '<?php endforeach; ?>';
    }

    /**
     * Compile the while statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Compile the end-while statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndWhile()
    {
        return '<?php endwhile; ?>';
    }

    /**
     * Compile the break statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileBreak($expression)
    {
        return $expression ? "<?php if{$expression} break; ?>" : '<?php break; ?>';
    }

    /**
     * Compile the continue statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileContinue($expression)
    {
        return $expression ? "<?php if{$expression} continue; ?>" : '<?php continue; ?>';
    }

    /**
     * Compile the forelse statements into valid PHP.
     * 
     *  @param  string  $expression
     * 
     * @return string
     */
    public function compileForElse($expression)
    {
        $empty = '$__empty_'.++$this->forElseCounter;
        
        return "<?php {$empty} = true; foreach{$expression}: {$empty} = false; ?>";
    }

    /**
     * Compile the for-else-empty statements into valid PHP.
     * 
     *  @param  string  $expression
     * 
     * @return string
     */
    public function compileEmpty($expression)
    {
        if ($expression)
        {
            return "<?php if(empty{$expression}): ?>";
        }

        $empty = '$__empty_'.$this->forElseCounter--;
        
        return "<?php endforeach; if ({$empty}): ?>";
    }

    /**
     * Compile the end-empty statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndEmpty()
    {
        return '<?php endif; ?>';
    }
    
    /**
     * Compile the end-foreach statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndForElse()
    {
        return '<?php endif; ?>';
    }
}