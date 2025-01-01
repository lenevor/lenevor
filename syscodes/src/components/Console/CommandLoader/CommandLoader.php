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
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\CommandLoader;

use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Console\Exceptions\CommandNotFoundException;

class CommandLoader
{
    /**
     * The container instance.
     * 
     * @var \Syscodes\Components\Contracts\Container\Container $container
     */
    protected Container $container;

    /**
     * Get the command map of the console.
     * 
     * @var array $commandMap
     */
    protected array $commandMap;

    /**
     * Constructor. Create a new CommandLoader class instance.
     * 
     * @param  \Syscodes\Components\Contracts\Container\Container  $container
     * @param  array  $commandMap
     * 
     * @return void
     */
    public function __construct(Container $container, array $commandMap)
    {
        $this->container = $container;
        $this->commandMap = $commandMap;
    }

    /**
     * Get the registered loader command.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     */
    public function get(string $name): Command
    {
        if ( ! $this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist', $name));
        }
        
        return $this->container->get($this->commandMap[$name]);
    }

    /**
     * Check if commandMap with $name exists.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
    }
    
    /**
     * Get all names keys of the command map.
     * 
     * @return array
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}