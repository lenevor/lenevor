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
 * Trait TranspilesHelpers.
 */
trait TranspilesHelpers
{
    /**
     * Transpile the CSRF statements into valid PHP.
     * 
     * @return string
     */
    protected function transpileCsrf(): string
    {
        return '<?php echo csrfField(); ?>';
    }

    /**
     * Transpile the 'dd' statements into valid PHP.
     * 
     * @param  string  $value
     * 
     * @return string
     */
    protected function transpileDd($value): string
    {
        return "<?php echo dd{$value}; ?>";
    }

    /**
     * Transpile the 'method' statements into valid PHP.
     * 
     * @param  string  $method
     * 
     * @return string
     */
    protected function transpileMethod($method): string
    {
        return "<?php echo methodField{$method}; ?>";
    }
}