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
 * @copyright   Copyright (c) 2019 - 2021 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\View\Transpilers\Concerns;

/**
 * Trait TranspilesLoops.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait TranspilesLoops
{
    /**
     * Counter to keep track of nested forelse statements.
     * 
     * @var int $forElseCounter
     */
    protected $forElseCounter = 0;

    /**
     * Transpile the for statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileFor($expression)
    {
        return "<?php for{$expression}: ?>";
    }

    /**
     * Transpile the end-for statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndFor()
    {
        return '<?php endfor; ?>';
    }

    /**
     * Transpile the foreach statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileForeach($expression)
    {
        return "<?php foreach{$expression}: ?>";
    }

    /**
     * Transpile the end-foreach statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndForeach()
    {
        return '<?php endforeach; ?>';
    }

    /**
     * Transpile the while statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileWhile($expression)
    {
        return "<?php while{$expression}: ?>";
    }

    /**
     * Transpile the end-while statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndWhile()
    {
        return '<?php endwhile; ?>';
    }

    /**
     * Transpile the break statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileBreak($expression)
    {
        return $expression ? "<?php if{$expression} break; ?>" : '<?php break; ?>';
    }

    /**
     * Transpile the continue statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileContinue($expression)
    {
        return $expression ? "<?php if{$expression} continue; ?>" : '<?php continue; ?>';
    }

    /**
     * Transpile the for-else statements into valid PHP.
     * 
     *  @param  string  $expression
     * 
     * @return string
     */
    protected function transpileForElse($expression)
    {
        $empty = '$__empty_'.++$this->forElseCounter;
        
        return "<?php {$empty} = true; foreach{$expression}: {$empty} = false; ?>";
    }

    /**
     * Transpile the for-else-empty statements into valid PHP.
     * 
     *  @param  string  $expression
     * 
     * @return string
     */
    protected function transpileEmpty($expression)
    {
        if ($expression) {
            return "<?php if(empty{$expression}): ?>";
        }

        $empty = '$__empty_'.$this->forElseCounter--;
        
        return "<?php endforeach; if ({$empty}): ?>";
    }

    /**
     * Transpile the end-empty statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndEmpty()
    {
        return '<?php endif; ?>';
    }
    
    /**
     * Transpile the end-for-else statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndForElse()
    {
        return '<?php endif; ?>';
    }
}