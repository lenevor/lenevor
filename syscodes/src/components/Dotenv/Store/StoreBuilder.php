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

namespace Syscodes\Components\Dotenv\Store;

use Syscodes\Components\Dotenv\Store\Contributors\Paths;

/**
 * Create a store builder for environment files.
 */
final class StoreBuilder
{
    /**
     * The of default name.
     * 
     * @var string[] DEFAULT_NAME
     */
    protected const DEFAULT_NAME = '.env';

    /**
     * Should file loading in enabled mode? 
     * 
     * @var bool $modeEnabled
     */
    protected $modeEnabled;

    /**
     * Get the file name .env.
     * 
     * @var string[] $names
     */
    protected $names;

    /**
     * The directory where the .env file is located.
     * 
     * @var string[] $paths
     */
    protected $paths;

    /**
     * Constructor. Create a new FileStore instance.
     * 
     * @param  string|string[]  $paths
     * @param  string|string[]  $names
     * @param  bool  $modeEnabled  (false by default)
     * 
     * @return void
     */
    public function __construct(array $paths = [], array $names = [], bool $modeEnabled = false)
    {
        $this->paths       = $paths;
        $this->names       = $names;
        $this->modeEnabled = $modeEnabled;
    }
    
    /**
     * Create a new file store instance with no names.
     * 
     * @return \Syscodes\Components\Dotenv\Store\StoreBuilder
     */
    public static function createWithNoNames()
    {
        return new self();
    }
    
    /**
     * Create a new file store instance with the default name.
     * 
     * @return \Syscodes\Components\Dotenv\Store\StoreBuilder
     */
    public static function createWithDefaultName()
    {
        return new self([], [self::DEFAULT_NAME]);
    }
    
    /**
     * Creates a file store with the given path added.
     * 
     * @param  string  $path
     * 
     * @return \Syscodes\Components\Dotenv\Store\StoreBuilder
     */
    public function addPath(string $path)
    {
        return new self(array_merge($this->paths, [$path]), $this->names);
    }
    
    /**
     * Creates a file store with the given name added.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Dotenv\Store\StoreBuilder
     */
    public function addName(string $name)
    {
        return new self($this->paths, array_merge($this->names, [$name]));
    }

    /**
     * Creates a store builder with mode enabled break.
     * 
     * @return \Syscodes\Components\Dotenv\Store\StoreBuilder
     */
    public function modeEnabled()
    {
        return new self($this->paths, $this->names, true);
    }
    
    /**
     * Creates a new store instance.
     * 
     * @return \Syscodes\Components\Dotenv\Store\FileStore
     */
    public function make()
    {
        return new FileStore(
            Paths::getFilePath($this->paths, $this->names),
            $this->modeEnabled
        );
    }
}