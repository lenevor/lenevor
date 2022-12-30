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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /LICENSE
 */

namespace Syscodes\Components\Dotenv\Repository\Adapters;

use Syscodes\Components\Contracts\Dotenv\Writer;

/**
 * Write and delete an environment variable.
 */
final class Writers implements Writer
{
    /**
     * The set of writers to use.
     * 
     * @var \Syscodes\Components\Dotenv\Repository\Adapters\Writers $writers
     */
    protected $writers;

    /**
     * Constructor. Create a new Writers instance.
     * 
     * @param  \Syscodes\Components\Dotenv\Repository\Adapters\Writers|array  $writers
     * 
     * @return void
     */
    public function __construct(array $writers)
    {
        $this->writers = $writers;
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
        foreach ($this->writers as $writes) {
            if ( ! $writes->write($name, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Write to an environment variable.
     * 
     * @param  string  $name
     * 
     * @return bool 
     */
    public function delete(string $name): bool
    {
        foreach ($this->writers as $writes) {
            if ( ! $writes->delete($name)) {
                return false;
            }
        }

        return true;
    }
}