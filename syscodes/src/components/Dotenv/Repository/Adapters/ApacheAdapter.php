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

namespace Syscodes\Dotenv\Repository\Adapters;

use Syscodes\Contracts\Dotenv\Adapter;

/**
 * Read, write and delete an environment variable for 
 * subprocess_env of Apache.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ApacheAdapter implements Adapter
{
    /**
     * Read an environment variable.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function read(string $name)
    {
        return apache_getenv($name);
    }

     /**
     * Write to an environment variable.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return bool
     */
    public function write(string $name, string $value)
    {
        return apache_setenv($name, $value);
    }

    /**
     * Delete an environment variable.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function delete(string $name)
    {
        return apache_setenv($name, '');
    }
}