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
 * @since       0.6.1
 */

namespace Syscodes\View\Transpilers\Concerns;

/**
 * Trait TranspilesComponents.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
trait TranspilesComponents
{
    /**
     * Transpile the component statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileComponent($expression)
    {
        return "<?php \$__env->beginComponent{$expression}; ?>";
    }

    /**
     * Transpile the end-component statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndComponent($expression)
    {
        return '<?php echo $__env->renderComponent(); ?>';
    }

    /**
     * Transpile the slot statements into valid PHP.
     * 
     * @param  string  $expression
     * 
     * @return string
     */
    protected function transpileSlot($expression)
    {
        return "<?php \$__env->slot{$expression}; ?>";
    }

    /**
     * Transpile the end-slot statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndSlot($expression)
    {
        return '<?php $__env->endSlot(); ?>';
    }
}