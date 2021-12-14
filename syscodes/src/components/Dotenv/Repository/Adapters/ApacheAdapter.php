<?php 

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
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
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /LICENSE
 */

namespace Syscodes\Components\Dotenv\Repository\Adapters;

use Syscodes\Components\Contracts\Dotenv\Adapter;

/**
 * Read, write and delete an environment variable for 
 * subprocess_env of Apache.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ApacheAdapter implements Adapter
{
    /**
     * Determines if the adapter is supported.
     * 
     * @return bool
     */
    public function isSupported(): bool
    {
        return function_exists('apache_getenv' && function_exists('apache_setenv'));
    }

    /**
     * Check if a variable exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        return false === apache_getenv($name);
    }

    /**
     * Read an environment variable.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    public function read(string $name)
    {
        if ($this->has($name)) {
            return apache_getenv($name);
        }

        return null;
    }

     /**
     * Write to an environment variable.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return bool
     */
    public function write(string $name, string $value): bool
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
    public function delete(string $name): bool
    {
        return apache_setenv($name, '');
    }
}