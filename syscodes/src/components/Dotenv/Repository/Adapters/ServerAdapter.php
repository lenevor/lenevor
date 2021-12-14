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
 * Read, write and delete an environment variable for $_SERVER.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ServerAdapter implements Adapter
{
    /**
     * Determines if the adapter is supported.
     * 
     * @return bool
     */
    public function isSupported(): bool
    {
        return true;
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
        return array_key_exists($name, $_SERVER);
    }

    /**
     * Read an environment variable.
     * 
     * @param  string  $name
     * 
     * @return array
     */
    public function read(string $name)
    {
        if ($this->has($name)) {
            return $_SERVER[$name];
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
        $notHttpName = 0 !== strpos($name, 'HTTP_');
        
        if ($notHttpName) {
            $_SERVER[$name] = $value;
        }

        return true;
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
        unset($_SERVER[$name]);

        return true;
    }
}