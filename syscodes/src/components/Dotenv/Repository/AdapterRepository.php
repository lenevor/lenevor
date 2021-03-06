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
use Syscodes\Dotenv\Repository\Adapters\Readers;
use Syscodes\Dotenv\Repository\Adapters\Writers;

/**
 * Gets to all the adapters.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class AdapterRepository implements Repository
{
    /**
     * The set of readers to use.
     * 
     * @var \Syscodes\Dotenv\Repository\Adapters\Readers $readers
     */
    protected $readers;

    /**
     * The set of writers to use.
     * 
     * @var \Syscodes\Dotenv\Repository\Adapters\Writers $writers
     */
    protected $writers;

    /**
     * Constructor. Create a new AdapterRepository instance.
     * 
     * @param  \Syscodes\Dotenv\Repository\Adapters\Readers  $readers
     * @param  \Syscodes\Dotenv\Repository\Adapters\Writers  $writers
     * 
     * @return void
     */
    public function __construct(Readers $readers, Writers $writers)
    {
        $this->readers = $readers;
        $this->writers = $writers;
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
        return $this->readers->read($name);
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
        return $this->writers->write($name, $value);
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
        return $this->writers->delete($name);
    }
}