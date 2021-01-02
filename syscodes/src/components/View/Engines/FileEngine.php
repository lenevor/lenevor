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
 * @copyright   Copyright (c) 2019-2021 Lenevor Framework 
 * @license     https://lenevor.com/license or see /license.md or see https://opensource.org/licenses/BSD-3-Clause New BSD license
 * @since       0.6.0
 */

namespace Syscodes\View\Engines;

use Syscodes\Contracts\View\Engine;

/**
 * The file engine.
 * 
 * @author Javier Alexander Campo M. <jalexcam@gmail.com>
 */
class FileEngine implements Engine
{
    /**
     * Get the evaluated contents of the view.
     * 
     * @param  string  $path
     * @param  array  $data
     * 
     * @return string
     */
    public function get($path, array $data = [])
    {
        return file_get_contents($path);
    }
}