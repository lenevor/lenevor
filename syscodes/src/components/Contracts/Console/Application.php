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

namespace Syscodes\Components\Contracts\Console;

use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * It allows to show the line header and the start of the Lenevor command console.
 */
interface Application
{
	/**
     * Gets the name of the application.
     * 
     * @return string 
     */
    public function getName(): string;

	/**
     * Sets the name of the application.
     * 
     * @param  string  $name  The application name
     * 
     * @return void
     */
    public function setName(string $name): void;

	/**
     * Gets the version of the application.
     * 
     * @return string
     */
    public function getVersion(): string;

	/**
     * Sets the name of the application.
     * 
     * @param  string  $version  The application version
     * 
     * @return void
     */
    public function setVersion(string $version): void;

	/**
	 * Runs the current command discovered on the CLI.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input|null  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output\Output|null  $output  The output interface implemented
     * 
     * @return int
	 */	
	public function run(?InputInterface $input = null, ?OutputInterface $output = null): int;

	/**
     * Executes the current application of console.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return int
     */
    public function doExecute(InputInterface $input, OutputInterface $output): int;

	/**
     * Adds a command object.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * 
     * @return \Syscodes\Components\Console\Command\Command|null
     * 
     * @throws \LogicException
     */
    public function addCommand(Command $command);

	/**
     * Gets input definition.
     * 
     * @return \Syscodes\Components\Console\Input\InputDefinition
     */
    public function getDefinition();

	/**
     * Finds a command by name.
     * 
     * @param  string  $name  The command name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     * 
     * @throws \Syscodes\Components\Console\Exceptions\CommandNotFoundException
     */
    public function findCommand(string $name): Command;

	/**
     * Gets a registered command.
     * 
     * @param  string  $name  The command name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     * 
     * @throws \Syscodes\Components\Console\Exceptions\CommandNotFoundException
     */
    public function get(string $name): Command;

	/**
     * Returns true if the command exists, false otherwise.
     * 
     * @param  string  $name  The command name
     * 
     * @return bool
     */
    public function has(string $name): bool;

	/**
     * Runs the current command.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command  The command name
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input  The input interface implemented
	 * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return int  0 if everything went fine, or an error code
     */
    public function doCommand(Command $command, InputInterface $input, OutputInterface $output): int;

	/**
     * Gets the help message.
     * 
     * @return string
     */
    public function getHelp(): string;
}