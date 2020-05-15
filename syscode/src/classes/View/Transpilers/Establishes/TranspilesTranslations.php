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

namespace Syscode\View\Transpilers\Establishes;

/**
 * Trait TranspilesTranslations.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
trait TranspilesTranslations
{
    /**
     * Transpile the lang statements into valid PHP.
     * 
     * @param  string  $expresion
     * 
     * @return string
     */
    protected function transpileLang($expression)
    {
        if (is_null($expression))
        {
            return '<?php \$__env->beginTranslation(); ?>';
        }
        elseif ($expression[1] === '[')
        {
            return "<?php \$__env->beginTranslation{$expression}; ?>";
        }

        return "<?php echo app('translator')->getLine{$expression}; ?>";
    }

    /**
     * Transpile the end-lang statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileEndlang()
    {
        return '<?php echo $__env->renderTranslation(); ?>';
    }
}