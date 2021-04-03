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

namespace Syscodes\Console;

use ReflectionException;
use Psr\Log\LoggerInterface;

/**
 * Class BaseCommand.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class BaseCommand
{
    /**
     * Gets the Command's Arguments description.
     * 
     * @var array $arguments
     */
    protected $arguments = [];

    /**
     * Gets the Commands so this commands can call other commands.
     * 
     * @var string $commands
     */
    protected $commands;

    /**
     * The console command description.
     * 
     * @var string|null $description
     */
    protected $description;

    /**
     * The group of commands is lumped under, when listing commands.
     * 
     * @var string $group
     */
    protected $group;

    /**
     * The logger to user for a command.
     * 
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * The console command name.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * The Command's options description.
     * 
     * @var array $options
     */
    protected $options = [];

    /**
     * The signature of the console command.
     * 
     * @var string $signature
     */
    protected $signature;

    /**
     * Constructor. Create a new base command instance.
     * 
     * @param  \Psr\Log\LoggerInterface  $logger
     * @param  \Syscodes\Console\Command  $command
     * 
     * @return void
     */
    public function __construct(LoggerInterface $logger, Command $commands)
    {
        $this->logger   = $logger;
        $this->commands = $commands;        
    }

    /**
     * Execute a command.
     * 
     * @param  array  $params
     * 
     * @return mixed
     */
    abstract public function run(array $params);

    /**
     * Is be used by a command to run other commands.
     * 
     * @param  string  $command
     * @param  array  $params
     * 
     * @return mixed
     * 
     * @throws \ReflectionException
     */
    public function call(string $command, array $params)
    {
        return $this->commands->run($command, $params);
    }

    /**
     * Show help includes:
     * - Usage
     * - Description
     * - Options
     * - Arguments
     * 
     * @return string
     */
    public function showHelp()
    {
        
    }
}