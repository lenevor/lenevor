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

namespace Syscodes\View\Transpilers\Concerns;

/**
 * Trait TranspilesConditionals.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait TranspilesConditionals
{
    /**
     * Identifier for the first case in switch statement.
     * 
     * @var bool $switchIdentifyFirstCase
     */
    protected $switchIdentifyFirstCase = true;

    /**
     * Transpile the if statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Transpile the else-if statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Transpile the else statements into valid PHP.
     *  
     * @return string
     */
    protected function transpileElse()
    {
        return '<?php else: ?>';
    }

    /**
     * Transpile the end-if statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndif()
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the if-isset statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileIsset($expression)
    {
        return "<?php if(isset{$expression}): ?>";
    }

    /**
     * Transpile the end-isset statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndIsset()
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the unless statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileUnless($expression)
    {
        return "<?php if( ! {$expression}): ?>";
    }

    /**
     * Transpile the end-unless statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndunless()
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the if statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileHasSection($expression)
    {
        return "<?php if( ! empty(trim(\$__env->hasSection{$expression}))): ?>";
    }

    /**
     * Transpile the switch statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileSwitch($expression)
    {
        $this->switchIdentifyFirstCase = true;

        return "<?php switch{$expression}:";
    }

    /**
     * Transpile the case statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileCase($expression)
    {
        if ($this->switchIdentifyFirstCase) {
            $this->switchIdentifyFirstCase = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    /**
     * Transpile the default statements in switch case into valid PHP.
     * 
     * @return string
     */
    protected function transpileDefault()
    {
        return '<?php default: ?>';
    }

    /**
     * Transpile the end-switch statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndSwitch()
    {
        return '<?php endswitch; ?>';
    }

    /**
     * Transpile the env statements into valid PHP.
     * 
     * @param  string  $environments
     * 
     * @return string
     */
    protected function transpileEnv($environments)
    {
        return "<?php if(app()->environment{$environments}): ?>";
    }

    /**
     * Transpile the end-env statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndEnv()
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the production statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileProduction()
    {
        return "<?php if(app()->environment('production')): ?>";
    }

    /**
     * Transpile the end-production statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndProduction()
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the testing statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileTesting()
    {
        return "<?php if(app()->environment('testing')): ?>";
    }

    /**
     * Transpile the end-testing statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndTesting()
    {
        return '<?php endif; ?>';
    }
}