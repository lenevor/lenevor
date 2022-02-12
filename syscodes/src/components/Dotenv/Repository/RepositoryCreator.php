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
 * @copyright   Copyright (c) 2019 - 2022 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Dotenv\Repository;

use ReflectionClass;
use InvalidArgumentException;
use InvalidaArgumentException;
use Syscodes\Components\Contracts\Dotenv\Adapter;
use Syscodes\Components\Dotenv\Repository\Adapters\Readers;
use Syscodes\Components\Dotenv\Repository\Adapters\Writers;
use Syscodes\Components\Dotenv\Repository\Adapters\EnvAdapter;
use Syscodes\Components\Dotenv\Repository\Adapters\ServerAdapter;

/**
 * Allows you to bring all the adapters.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
final class RepositoryCreator
{
    protected static $adapterDefault = [
        EnvAdapter::class,
        ServerAdapter::class,
    ];

    /**
     * Gets adapters allow list to use.
     * 
     * @var string[] $allowlist
     */
    protected $allowlist;

    /**
     * The set of readers to use.
     * 
     * @var array|\Syscodes\Components\Dotenv\Repository\Adapters\Readers $readers
     */
    protected $readers;

    /**
     * The set of writers to use.
     * 
     * @var array|\Syscodes\Components\Dotenv\Repository\Adapters\Writers$writers
     */
    protected $writers;

    /**
     * Constructor. Create a new Repository creator instance.
     * 
     * @param  array|\Syscodes\Components\Dotenv\Repository\Adapters\Readers  $readers
     * @param  array|\Syscodes\Components\Dotenv\Repository\Adapters\Writers  $writers
     * @param  string[]|null  $allowList
     * 
     * @return void
     */
    public function __construct(array $readers = [], array $writers = [], array $allowList = null)
    {
        $this->readers   = $readers;
        $this->writers   = $writers;
        $this->allowList = $allowList;
    }

    /**
     * Create a new repository creator instance with the default adapters added.
     * 
     * @return \Syscodes\Components\Dotenv\Repository\RepositoryCreator
     */
    public static function createDefaultAdapters()
    {
        $adapters = iterator_to_array(static::defaultAdapters());
        
        return new static($adapters, $adapters);
    }
    
    /**
     * Return the array of default adapters.
     * 
     * @return \Syscodes\Components\Contracts\Dotenv\Adapter|object
     */
    protected static function defaultAdapters()
    {
        foreach (static::$adapterDefault as $adapter) {
            yield new $adapter;
        }
    }

    /**
     * Determine if the given name if of an adapter class.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    protected static function inAdapterClass(string $name): bool
    {
        if ( ! class_exists($name))
        {
            return false;
        }

        return (new ReflectionClass($name))->implementsInterface(Adapter::class);
    }

    /**
     * Creates a repository builder with the given reader added.
     * 
     * @param  string  $adapter
     * 
     * @return new static
     * 
     * @return \InvalidArgumentException
     */
    public function addAdapter(string $adapter)
    {
        if ( ! is_string($adapter) && ! ($adapter instanceof Adapter)) {
            throw new InvalidArgumentException("Expected either an instance of [{$this->allowList}]");
        }

        $adapter = $this->getReflectionClass($adapter);

        $readers = array_merge($this->readers, [$adapter]);
        $writers = array_merge($this->writers, [$adapter]);

        return new static($readers, $writers, $this->allowList);
    }

    /**
     * Gets class.
     * 
     * @param  string  $class
     * 
     * @return object
     */
    protected function getReflectionClass($class): object
    {
        $object = new ReflectionClass($class);

        return $object->newInstanceWithoutConstructor();
    }

    /**
     * Creates a new repository instance.
     * 
     * @return \Syscodes\Components\Dotenv\Repository\AdapterRepository
     */
    public function make()
    {
        $readers = new Readers($this->readers);
        $writers = new Writers($this->writers);

        return new AdapterRepository($readers, $writers);
    }
}