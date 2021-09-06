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

use Exception;
use Syscodes\Console\Command\Command;
use Syscodes\Console\Input\ArgvInput;
use Syscodes\Support\Facades\Request;
use Syscodes\Console\Input\ArrayInput;
use Syscodes\Console\Input\InputOption;
use Syscodes\Console\Command\HelpCommand;
use Syscodes\Console\Command\ListCommand;
use Syscodes\Console\Input\InputArgument;
use Syscodes\Console\Output\ConsoleOutput;
use Syscodes\Console\Input\InputDefinition;
use Syscodes\Console\Formatter\OutputFormatter;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * This is the main entry point of a Console application.
 * 
 * This class is optimized for a standard CLI environment.
 * 
 * @author Alexander Campo <jalexcam@gmail.com> 
 */
abstract class Console
{
    /**
     * Gets the command name.
     * 
     * @var array $commands
     */
    protected $commands = [];
    
    /**
	 * The default command.
	 * 
	 * @var string $defaultCommand
	 */
	protected $defaultCommand;

    /**
     * The InputDefinition implement.
     * 
     * @var \Syscodes\Console\Input\InputDefinition $definition
     */
    protected $definition;

    /**
     * Gets the name of the aplication.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * The single command.
     * 
     * @var bool $singleCommand
     */
    protected $singleCommand = false;

    /**
     * Gets the version of the application.
     * 
     * @var string $version
     */
    protected $version;

    /**
     * Constructor. Create new Console instance.
     * 
     * @param  string  $name  The console name
     * @param  string  $version  The console version
     * 
     * @return void
     */
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        $this->name    = $name;
        $this->version = $version;
        
		$this->defaultCommand = 'list';
    }

    /**
     * Gets the name of the application.
     * 
     * @return string 
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the application.
     * 
     * @param  string  $name  The application name
     * 
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Gets the version of the application.
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the name of the application.
     * 
     * @param  string  $version  The application version
     * 
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
	 * Runs the current command discovered on the CLI.
	 * 
	 * @param  \Syscodes\Contracts\Console\Input|null  $input  The input interface implemented
	 * @param  \Syscodes\Contracts\Console\Output|null  $output  The output interface implemented
	 *
	 * @return int
	 */
	public function run(InputInterface $input = null, OutputInterface $output = null)
	{
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        try {
            $exitCode = $this->doExecute($input, $output);
        } catch (Exception $e) {
            throw $e;

            $exitCode = $e->getCode();
        }

        return $exitCode;
	}

    /**
     * Executes the current application of console.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input  The input interface implemented
	 * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return int
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(['--version', '-V'], true)) {
            $output->writeln($this->getConsoleVersion());

            return 0;
        }
    }

    /**
     * Returns the version of the console.
     *
     * @return string
     */
    public function getConsoleVersion()
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return sprintf('%s <info>%s</info> (env: <comment>%s</comment>, debug: <comment>%s</comment>) [<magenta>%s</magenta>]', 
                    $this->getName(), 
                    $this->getVersion(),
                    env('APP_ENV'),
                    env('APP_DEBUG') ? 'true' : 'false',
			        PHP_OS
                );
            }

            return $this->getName();
        }

        return 'Lenevor CLI Console';
    }

    /**
     * Gets the name of the command based on input.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input
     * 
     * @return string|null
     */
    protected function getCommandName(InputInterface $input)
    {
        return $this->singleCommand ? $this->defaultCommand : $input->getFirstArgument();
    }

    /**
     * Gets the default commands that should always be available.
     * 
     * @return array
     */
    protected function getDefaultCommands(): array
    {
        return [new HelpCommand(), new ListCommand()];
    }
}