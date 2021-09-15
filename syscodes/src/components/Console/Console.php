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
use Syscodes\Console\IO\Interactor;
use Syscodes\Console\Command\Command;
use Syscodes\Console\Input\ArgvInput;
use Syscodes\Console\Input\ArrayInput;
use Syscodes\Console\Command\HelpCommand;
use Syscodes\Console\Command\ListCommand;
use Syscodes\Console\Input\InputArgument;
use Syscodes\Console\Output\ConsoleOutput;
use Syscodes\Console\Input\InputDefinition;
use Syscodes\Contracts\Console\InputOption;
use Syscodes\Console\Concerns\ApplicationHelp;
use Syscodes\Console\Formatter\OutputFormatter;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Contracts\Console\InputOption as InputOptionInterface;
use Syscodes\Contracts\Console\InputArgument as InputArgumentInterface;

/**
 * This is the main entry point of a Console application.
 * 
 * This class is optimized for a standard CLI environment.
 * 
 * @author Alexander Campo <jalexcam@gmail.com> 
 */
abstract class Console
{
    use ApplicationHelp;
    
    /**
     * The list of internal commands.
     * 
     * @var array $internalCommands
     */
    protected static $internalCommands = [
        'version' => 'Displays the application version',
        'about'   => 'Displays information about the current project',
        'help'    => 'Displays help for a command',
        'list'    => 'Lists commands'
    ];

    /**
     * Application config data.
     * 
     * @var array $config
     */
    protected $config = [
        'homepage'   => '',
        'publishAt'  => '02.05.2019',
        'updateAt'   => '13.09.2021',
        'logoText'   => '',
        'logoStyle'  => 'info',
    ];

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
     * Command delimiter.
     * 
     * @var string $delimiter
     */
    protected $delimiter = ':';

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

        $this->configureIO($input, $output);

        try {
            $exitCode = $this->doExecute($input, $output);
        } catch (Exception $e) {
            throw $e;

            $exitCode = $e->getCode();
        }

        return $exitCode;
	}

    /**
     * Configures the input and output instances.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input  The input interface implemented
	 * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return \Syscodes\Console\IO\Interactor
     */
    protected function configureIO($input, $output)
    {
        return (new Interactor($input, $output))->getConfigureIO();
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
        if ($this->filterCommands($input, $output)) {
            return 0;
        }
        
        try {
            $input->linked($this->getDefinition());
        } catch (Exception $e) {}       
    }

    /**
     * Gets the filter commands.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input  The input interface implemented
     * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
     * @param  string|null  $command  The command name
     * 
     * @return bool
     */
    protected function filterCommands($input, $output, ?string $command = null): bool
    {
        if ( ! $command) {
            if ($input->hasParameterOption(GlobalOption::VERSION_OPTION, true)) {
                $this->displayVersionInfo($output);

                return true;
            }

            $command = $this->defaultCommand;
        } elseif ( ! static::$internalCommands) {
            return false;
        }

        switch($command) {
            case 'version':
                $this->displayVersionInfo($output);
                break;
            case 'list':
                $output->writeln($this->getConsoleVersion());
                $output->writeln("\n".'<yellow>No elements for listing</yellow>');                
                break;
            default:
                false;
        }

        return true;
    }
    
    /**
     * Gets internal command.
     * 
     * @param  string  $name
     * 
     * @return bool
     */
    public function isInternalCommand(string $name): bool
    {
        return isset(static::$internalCommands[$name]);
    }

    /**
     * Adds a command object.
     * 
     * @param  \Syscodes\Console\Command\Command  $command
     * 
     * @return \Syscodes\Console\Command\Command
     */
    public function add(Command $command)
    {
        $this->initialize();

        $this->commands[$command->getName()] = $command;

        return $this;
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
     * Getsinput definition.
     * 
     * @return \Syscodes\Console\Input\InputDefinition
     */
    public function getDefinition() 
    {
        if ( ! $this->definition) {
            $this->definition = $this->getDefaultInputDefinition();
        }

        if ($this->singleCommand) {
            $inputDefinition = $this->definition;
            $inputDefinition->setArguments();
            
            return $inputDefinition;
        }
        
        return $this->definition;
    }

    /**
     * Gets the default input definition.
     * 
     * @return \Syscodes\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputArgument('command', InputArgumentInterface::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOptionInterface::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <info>'.$this->defaultCommand.'</info> command'),
            new InputOption('--quiet', '-q', InputOptionInterface::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOptionInterface::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOptionInterface::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOptionInterface::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', false)
        ]);
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

    /**
     * Gets the logo text for console app.
     * 
     * @return string|null
     */
    public function getLogoText(): string
    {
        return $this->config['logoText'] ?? null;
    }

    /**
     * Sets the logo text for console app.
     * 
     * @param  string  $logoText
     * @param  striong|null  $style
     * 
     * @return void
     */
    public function setLogo(string $logoText, string $style = null): void
    {
        $this->config['logoText'] = $logoText;

        if ($style) {
            $this->config['logoStyle'] = $style;
        }
    }

    /**
     * Gets the logo style for console app.
     * 
     * @return string|null 
     */
    public function getLogoStyle(): ?string
    {
        return $this->config['logoStyle'] ?? 'info';
    }

    /**
     * Sets the logo style for console app.
     * 
     * @param  string  $style
     * 
     * @return void
     */
    public function setLogoStyle(string $style): void
    {
        $this->config['logoStyle'] = $style;
    }

    /**
     * Get config param value.
     * 
     * @param  string  $name
     * @param  string|null  $default
     * 
     * @return array|string
     */
    public function getParam(string $name, $default = null)
    {
        return $this->config[$name] ?? $default;
    }
}