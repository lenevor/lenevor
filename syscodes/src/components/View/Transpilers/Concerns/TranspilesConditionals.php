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
 * Trait TranspilesConditionals.
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
    protected function transpileIf($expression): string
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
    protected function transpileElseif($expression): string
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Transpile the else statements into valid PHP.
     *  
     * @return string
     */
    protected function transpileElse(): string
    {
        return '<?php else: ?>';
    }

    /**
     * Transpile the end-if statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndif(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the if-auth statements into valid PHP.
     * 
     * @param  string|null  $guard
     * 
     * @return string
     */
    protected function transpileAuth($guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php if(auth()->guard{$guard}->check()): ?>";
    }
    
    /**
     * Transpile the else-auth statements into valid PHP.
     * 
     * @param  string|null  $guard
     * 
     * @return string
     */
    protected function transpileElseAuth($guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;
        
        return "<?php elseif(auth()->guard{$guard}->check()): ?>";
    }
    
    /**
     * Transpile the end-auth statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndAuth(): string
    {
        return '<?php endif; ?>';
    }
    
    /**
     * Transpile the if-guest statements into valid PHP.
     * 
     * @param  string|null  $guard
     * 
     * @return string
     */
    protected function compileGuest($guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;
        
        return "<?php if(auth()->guard{$guard}->guest()): ?>";
    }
    
    /**
     * Transpile the else-guest statements into valid PHP.
     * 
     * @param  string|null  $guard
     * 
     * @return string
     */
    protected function compileElseGuest($guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;
        
        return "<?php elseif(auth()->guard{$guard}->guest()): ?>";
    }
    
    /**
     * Transpile the end-guest statements into valid PHP.
     * 
     * @return string
     */
    protected function compileEndGuest(): string
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
    protected function transpileIsset($expression): string
    {
        return "<?php if(isset{$expression}): ?>";
    }

    /**
     * Transpile the end-isset statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndIsset(): string
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
    protected function transpileUnless($expression): string
    {
        return "<?php if( ! {$expression}): ?>";
    }

    /**
     * Transpile the end-unless statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndunless(): string
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
    protected function transpileHasSection($expression): string
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
    protected function transpileSwitch($expression): string
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
    protected function transpileCase($expression): string
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
    protected function transpileDefault(): string
    {
        return '<?php default: ?>';
    }

    /**
     * Transpile the end-switch statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndSwitch(): string
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
    protected function transpileEnv($environments): string
    {
        return "<?php if(app()->environment{$environments}): ?>";
    }

    /**
     * Transpile the end-env statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndEnv(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the production statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileProduction(): string
    {
        return "<?php if(app()->environment('production')): ?>";
    }

    /**
     * Transpile the end-production statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndProduction(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the testing statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileTesting(): string
    {
        return "<?php if(app()->environment('testing')): ?>";
    }

    /**
     * Transpile the end-testing statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndTesting(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Transpile the local statements into valid PHP.
     * 
     * @return string
     */
    protected function transpilelocal(): string
    {
        return "<?php if(app()->environment('local')): ?>";
    }

    /**
     * Transpile the end-local statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndlocal(): string
    {
        return '<?php endif; ?>';
    }
}