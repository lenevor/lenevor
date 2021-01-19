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

use Syscodes\Contracts\Dotenv\Adapter;
use Syscodes\Dotenv\Repository\Adapters\ArrayAdapter;
use Syscodes\Dotenv\Repository\Adapters\ApacheAdapter;
use Syscodes\Dotenv\Repository\Adapters\PutenvAdapter;
use Syscodes\Dotenv\Repository\Adapters\ServerAdapter;

/**
 * Allows you to bring all the adapters.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class RepositoryCreator
{
    protected const ADAPTERS_DEFAULT = [
        ApacheAdapter::class,
        ArrayAdapter::class,
        PutenvAdapter::class,
        ServerAdapter::class,
    ];

    /**
     * Gets adapters allow list to use.
     * 
     * @var string[] $allowlist
     */
    protected $allowlist;

    /**
     * Constructor. Create a new Repository creator instance.
     * 
     * @param  \Syscodes\Contracts\Dotenv\Adapter[]  $adapter
     * 
     * @return void
     */
    public function __construct(array $adapter = [])
    {
        $this->allowList = $adapter;
    }

    /**
     * Create a new repository creator instance with the default adapters added.
     * 
     * @return \Syscodes\Dotenv\Repository\RepositoryCreator
     */
    public function createDefaultAdapters()
    {
        $adapters = iterator_to_array(self::defaultAdapters());

        return new static($adapters);
    }
    
    /**
     * Return the array of default adapters.
     * 
     * @return \Syscodes\Contracts\Dotenv\Adapter
     */
    protected static function defaultAdapters()
    {
        foreach (self::ADAPTERS_DEFAULT as $adapter) {
            yield new $adapter;
        }
    }

    /**
     * Creates a new repository instance.
     * 
     * @return \Syscodes\Contracts\Dotenv\Repository
     */
    public function make()
    {
        return new AdapterRepository($this->allowlist);
    }
}