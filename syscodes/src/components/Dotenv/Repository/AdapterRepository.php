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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Dotenv\Repository;

use Syscodes\Components\Contracts\Dotenv\Reader;
use Syscodes\Components\Contracts\Dotenv\Writer;
use Syscodes\Components\Contracts\Dotenv\Repository;

/**
 * Gets to all the adapters.
 */
final class AdapterRepository implements Repository
{
    /**
     * The set of readers to use.
     * 
     * @var \Syscodes\Components\Contracts\Dotenv\Reader $reader
     */
    protected $reader;

    /**
     * The set of writers to use.
     * 
     * @var \Syscodes\Components\Contracts\Dotenv\Writer $writer
     */
    protected $writer;

    /**
     * Constructor. Create a new AdapterRepository instance.
     * 
     * @param  \Syscodes\Components\Contracts\Dotenv\Reader  $reader
     * @param  \Syscodes\Components\Contracts\Dotenv\Writer  $writer
     * 
     * @return void
     */
    public function __construct(Reader $reader, Writer $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    /**
     * Get an environment variable.
     * 
     * @param  string  $name
     * 
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->reader->read($name);
    }

    /**
     * Set an environment variable.
     * 
     * @param  string  $name
     * @param  string  $value
     * 
     * @return bool
     */
    public function set(string $name, string $value): bool
    {
        return $this->writer->write($name, $value);
    }

    /**
     * Clear an environment variable.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function clear(string $name): bool
    {
        return $this->writer->delete($name);
    }
}