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
 * Trait CompilesLayouts.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait CompilesLayouts
{
	/**
     * Compile the extends statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function compileExtends($expression)
    {
        $expression = $this->stripParentheses($expression);

        $data = "<?php echo \$__env->extendsLayout({$expression}); ?>";

        $this->footer[] = $data;

        return '';
    }

    /**
     * Compile the section statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function compileSection($expression)
    {
        return "<?php \$__env->beginSection{$expression}; ?>";
    }

    /**
     * Compile the yield statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function compileGive($expression)
    {
        return "<?php echo \$__env->giveContent{$expression}; ?>";
    }

    /**
     * Compile the append statements into valid PHP.
     * 
     * @return string
     */
    protected function compileAppend()
    {
        return "<?php \$__env->appendSection(); ?>";
    }

    /**
     * Compile the show statements into valid PHP.
     * 
     * @return string
     */
    protected function compileShow()
    {
        return "<?php echo \$__env->showSection(); ?>";
    }

    /**
     * Compile the end-section statements into valid PHP.
     * 
     * @return string
     */
    protected function compileStop()
    {
        return "<?php \$__env->stopSection(); ?>";
    }
}