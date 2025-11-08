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

namespace Syscodes\Components\Console\Description;

use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Exceptions\CommandNotFoundException;

/**
 * @internal
 */
class ApplicationDescription
{
    public const G_NAMESPACE = '_global';

    /**
     * Get the aliases of the commands.
     * 
     * @var array $aliases
     */
    protected $aliases = [];

    /**
     * The application console.
     * 
     * @var \Syscodes\Components\Console\Application
     */
    protected $application;

    /**
     * Get the commands.
     * 
     * @var array 
     */
    protected array $commands;

    /**
     * Get the namespace of commands.
     * 
     * @var string|null $namespace
     */
    protected ?string $namespace;

    /**
     * Get the namespaces.
     * 
     * @var array $namespaces
     */
    protected array $namespaces;

    /**
     * Get the hidden data in boolean.
     * 
     * @var bool $hidden
     */
    protected bool $hidden;

    /**
     * Constructor. Create a new ApplicationDescription instance.
     * 
     * @param  \Syscodes\Components\Console\Application  $application
     * @param  string|null  $namespace
     * @param  bool  $hidden
     * 
     * @return void
     */
    public function __construct(Application $application, ?string $namespace = null, bool $hidden = false)
    {
        $this->application = $application;
        $this->namespace = $namespace;
        $this->hidden = $hidden;
    }

    /**
     * Get all the commands of console.
     * 
     * @return array|\Syscodes\Components\Console\Command\Command
     */
    public function getCommands(): array
    {
        if ( ! isset($this->commands)) {
            $this->initAplication();
        }

        return $this->commands;
    }

    /**
     * Get a command of console.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     */
    public function getCommand(string $name): Command
    {
        if ( ! isset($this->commands[$name]) && ! isset($this->aliases[$name])) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist', $name));
        }
        
        return $this->commands[$name] ?? $this->aliases[$name];
    }

    /**
     * Get all the namespaces of commands.
     * 
     * @return array|void
     */
    public function getNamespaces()
    {
        if ( ! isset($this->namespaces)) {
            return $this->initAplication();
        }

        return $this->namespaces;
    }

    /**
     * Get the initialize to application of console.
     * 
     * @return void
     */
    private function initAplication(): void
    {
        $this->commands = [];
        $this->namespaces = [];
        
        $all = $this->application->all($this->namespace ? $this->application->findNamespace($this->namespace) : null);
        
        foreach ($this->sortCommands($all) as $namespace => $commands) {
            $names = [];
            
            /** @var \Syscodes\Components\Console\Command\Command $command */
            foreach ($commands as $name => $command) {
                if ( ! $command->getName() || ( ! $this->hidden && $command->isHidden())) {
                    continue;
                }
                
                if ($command->getName() === $name) {
                    $this->commands[$name] = $command;
                } else {
                    $this->aliases[$name] = $command;
                }
                
                $names[] = $name;
            }
            
            $this->namespaces[$namespace] = ['id' => $namespace, 'commands' => $names];
        }
    }

    /**
     * Get the sort commands order.
     * 
     * @param  array  $commands
     * 
     * @return array
     */
    private function sortCommands(array $commands): array
    {
        $namespacedCommands = [];
        $globalCommands = [];
        $sortedCommands = [];
        
        foreach ($commands as $name => $command) {
            $key = $this->application->extractNamespace($name, 1);
            
            if (in_array($key, ['', self::G_NAMESPACE], true)) {
                $globalCommands[$name] = $command;
            } else {
                $namespacedCommands[$key][$name] = $command;
            }
        }
        
        if ($globalCommands) {
            ksort($globalCommands);
            $sortedCommands[self::G_NAMESPACE] = $globalCommands;
        }
        
        if ($namespacedCommands) {
            ksort($namespacedCommands, SORT_STRING);
            
            foreach ($namespacedCommands as $key => $commandsSet) {
                ksort($commandsSet);
                $sortedCommands[$key] = $commandsSet;
            }
        }
        
        return $sortedCommands;
    }
}