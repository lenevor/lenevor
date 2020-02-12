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
 * Trait CompilesConditionals.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait CompilesConditionals
{
    /**
     * Identifier for the first case in switch statement.
     * 
     * @var bool $switchIdentifyFirstCase
     */
    protected $switchIdentifyFirstCase = true;

    /**
     * Compile the if statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the else statements into valid PHP.
     *  
     * @return string
     */
    public function compileElse()
    {
        return '<?php else: ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndif()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if-isset statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileIsset($expression)
    {
        return "<?php if(isset{$expression}): ?>";
    }

    /**
     * Compile the end-isset statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndIsset()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the unless statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileUnless($expression)
    {
        return "<?php if( ! {$expression}): ?>";
    }

    /**
     * Compile the end-unless statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndunless()
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileHasSection($expression)
    {
        return "<?php if( ! empty(trim(\$__env->hasSection{$expression}))): ?>";
    }

    /**
     * Compile the switch statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileSwitch($expression)
    {
        $this->switchIdentifyFirstCase = true;

        return "<?php switch{$expression}: ?>";
    }

    /**
     * Compile the case statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    public function compileCase($expression)
    {
        if ($this->switchIdentifyFirstCase)
        {
            $this->switchIdentifyFirstCase = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    /**
     * Compile the default statements in switch case into valid PHP.
     * 
     * @return string
     */
    public function compileDefault()
    {
        return '<?php default: ?>';
    }

    /**
     * Compile the end-switch statements into valid PHP.
     * 
     * @return string
     */
    public function compileEndSwitch()
    {
        return '<?php endswitch; ?>';
    }
}