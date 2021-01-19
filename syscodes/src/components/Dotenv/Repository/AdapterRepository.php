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

namespace Syscodes\Dotenv\Repository;

use Syscodes\Contracts\Dotenv\Repository;

/**
 * Gets to all the adapters.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class AdapterRepository implements Repository
{
    /**
     * Get an adapter to use.
     * 
     * @var \Syscodes\Contracts\Dotenv\Adapter $adapter
     */
    protected $adapter;

    /**
     * Constructor. Create a new AdapterRepository instance.
     * 
     * @param  \Syscodes\Contracts\Dotenv\Adapter  $adapter
     * 
     * @return void
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get an environment variable.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function get(string $name)
    {
        return $this->adapter->read($name);
    }

     /**
     * Set an environment variable.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return bool
     */
    public function set(string $name, string $value)
    {
        return $this->adapter->write($name);
    }

    /**
     * Clear an environment variable.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function clear(string $name)
    {
        return $this->adapter->delete($name);
    }
}