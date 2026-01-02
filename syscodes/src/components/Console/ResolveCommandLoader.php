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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Resolve command loader.
 */
class ResolveCommandLoader implements CommandLoaderInterface
{
    /**
     * The container instance.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * A map of command names to classes.
     *
     * @var array
     */
    protected $commandMap;

    /**
     * Constructor. Create a new command loader instance.
     *
     * @param  \Psr\Container\ContainerInterface  $container
     * @param  array  $commandMap
     * 
     * @return void
     */
    public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container = $container;
        $this->commandMap = $commandMap;
    }

    /**
     * Resolve a command from the container.
     *
     * @param  string  $name
     * 
     * @return \Symfony\Component\Console\Command\Command
     *
     * @throws \Syscodes\Components\Console\Exception\CommandNotFoundException
     */
    public function get(string $name): Command
    {
        if (! $this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        return $this->container->get($this->commandMap[$name]);
    }

    /**
     * Determines if a command exists.
     *
     * @param  string  $name
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        return $name && isset($this->commandMap[$name]);
    }

    /**
     * Get the command names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}