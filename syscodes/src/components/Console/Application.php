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

namespace Syscodes\Components\Console;

use Exception;
use Throwable;
use LogicException;
use Syscodes\Components\Console\IO\Interactor;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Input\ArgvInput;
use Syscodes\Components\Console\Input\ArrayInput;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Command\HelpCommand;
use Syscodes\Components\Console\Command\ListCommand;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Output\ConsoleOutput;
use Syscodes\Components\Console\Input\InputDefinition;
use Syscodes\Components\Console\Concerns\BuildConsoleVersion;
use Syscodes\Components\Contracts\Console\Input as InputInterface;
use Syscodes\Components\Console\Exceptions\CommandNotFoundException;
use Syscodes\Components\Contracts\Console\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Application as ApplicationContract;
use Syscodes\Components\Contracts\Console\InputOption as InputOptionInterface;
use Syscodes\Components\Contracts\Console\InputArgument as InputArgumentInterface;

/**
 * This is the main entry point of a Console application.
 * 
 * This class is optimized for a standard CLI environment.
 * 
 * @author Alexander Campo <jalexcam@gmail.com> 
 */
class Application implements ApplicationContract
{
    use BuildConsoleVersion;

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
     * @var \Syscodes\Components\Console\Input\InputDefinition $definition
     */
    protected $definition;

    /**
     * Command delimiter.
     * 
     * @var string $delimiter
     */
    protected $delimiter = ':';

    /**
     * Indicates if this activate the command help.
     * 
     * @var bool $helps
     */
    protected $helps = false;

    /**
     * Gets the initialize of commands.
     * 
     * @var bool $initialize
     */
    protected $initialize = false;

    /**
     * Gets the name of the aplication.
     * 
     * @var string $name
     */
    protected $name;

    protected $runCommand;

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
     * @inheritdoc 
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
    
    /**
     * @inheritdoc
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
     * @param  \Syscodes\Components\Contracts\Console\Input  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return \Syscodes\Components\Console\IO\Interactor
     */
    protected function configureIO($input, $output)
    {
        return (new Interactor($input, $output))->getConfigureIO();
    }
    
    /**
     * @inheritdoc
     */
    public function doExecute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasParameterOption(GlobalOption::VERSION_OPTION, true)) {
            $output->writeln($this->getConsoleVersion());

            return 0;
        }

        try {
            $input->linked($this->getDefinition());
        } catch (Exception $e) {
            throw $e;
        }
        
        $name = $this->getCommandName($input);

        if (true === $input->hasParameterOption(GlobalOption::HELP_OPTION, true)) {
            if ( ! $name) {
                $name = 'help';
                $input = new ArrayInput(['command_name' => $this->defaultCommand]);
            } else {
                $this->helps = true;
            }
        }

        if ( ! $name) {
            $name = $this->defaultCommand;
            $definition = $this->getDefinition();
            $definition->setArguments(array_merge(
                $definition->getArguments(),
                [
                    'command' => new InputArgument('command', InputArgumentInterface::OPTIONAL, $definition->getArgument('command')->getDescription(), $name),
                ]
            ));
        }
                
        try {
            $this->runCommand = null;
            $command = $this->findCommand($name);
        } catch(Throwable $e) {
            if ( ! ($e instanceof CommandNotFoundException) || 1 !== \count($alternatives = $e->getAlternatives())) {
                throw $e;
            }

            $alternative = $alternatives[0];

            $output->write(sprintf('Command "%s" not defined', $name));

            $command = $this->findCommand($alternative);
        }
        
        $this->runCommand = $command;
        $exitCode = $this->doCommand($command, $input, $output);
        $this->runCommand = null;

        return $exitCode;
    }

    /**
     * @inheritdoc
     */
    public function addCommand(Command $command)
    {
        $this->initialize();

        $command->setApplication($this);
        
        if ( ! $command->isEnabled()) {
            $command->setApplication(null);
            
            return null;
        }
        
        if ( ! $command->getName()) {
            throw new LogicException(sprintf('The command defined in "%s" cannot have an empty name', get_debug_type($command)));
        }
        
        $this->commands[$command->getName()] = $command;
        
        foreach ($command->getAliases() as $alias) {
            $this->commands[$alias] = $command;
        }
        
        return $command;
    }

    /**
     * Gets the name of the command based on input.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input  $input
     * 
     * @return string|null
     */
    protected function getCommandName(InputInterface $input)
    {
        return $this->singleCommand ? $this->defaultCommand : $input->getFirstArgument();
    }

    /**
     * @inheritdoc
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
     * @internal
     */
    public function isSingleCommand(): bool
    {
        return $this->singleCommand;
    }

    /**
     * Gets the default input definition.
     * 
     * @return \Syscodes\Components\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputArgument('command', InputArgumentInterface::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOptionInterface::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <comment>'.$this->defaultCommand.'</comment> command'),
            new InputOption('--quiet', '-q', InputOptionInterface::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOptionInterface::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOptionInterface::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOptionInterface::VALUE_NEGATABLE, 'Force (or disable --no-color) ANSI output', false),
            new InputOption('--no-interaction', '-n', InputOptionInterface::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findCommand(string $name)
    {
        $this->initialize();

        foreach ($this->commands as $command) {
            foreach ($command->getAliases() as $alias) {
                if ( ! $this->has($alias)) {
                    $this->commands[$alias] = $command;
                }
            }
        }
        
        if ($this->has($name)) {
            return $this->get($name);
        }

        $allCommands = array_keys($this->commands);
        $expression  = implode('[^:]*:', array_map('preg_quote', explode(':', $name))).'[^:]*';
        $commands    = preg_grep('{^'.$expression.'}', $allCommands);

        if (empty($commands)) {
            $commands = preg_grep('{^'.$expression.'}i', $allCommands);
        }

        $command = $this->get(headItem($commands));
        
        if ($command->isHidden()) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist', $name));
        }

        return $command;
    }

    /**
     * @inheritdoc
     */
    public function get(string $name)
    {
        $this->initialize();

        if ( ! $this->has($name)) {
            throw new CommandNotFoundException(
                sprintf('The "%s" command cannot be found because it is registered under multiple names. Make sure you don\'t set a different name via constructor or "setName()"', $name)
            );
        }
        
        if ( ! isset($this->commands[$name])) {
            throw new CommandNotFoundException(
                sprintf('The "%s" command cannot be found because it is registered under multiple names. Make sure you don\'t set a different name via constructor or "setName()".', $name)
            );
        } 

        $command = $this->commands[$name];

        if ($this->helps) {
            $this->helps = false;            
            
            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);
           
            return $helpCommand;
        }   

        return $command;
    }

    /**
     * @inheritdoc
     */
    public function has(string $name): bool
    {
        $this->initialize();

        return isset($this->commands[$name]) || $this->addCommand($this->get($name));
    }

    /**
     * @inheritdoc
     */
    public function doCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        try {
            $input->linked($command->getDefinition());
        } catch (Exception $e) {
            // ignore invalid options/arguments for now, to allow the event listeners to customize the InputDefinition
        }

        try {
            $exitCode = $command->run($input, $output);
        } catch (Throwable $e) {
            throw $e;
        }

        return $exitCode;
    }

    /**
     * Gets all the namespaces used by currently registered commands.
     * 
     * @return string[]
     */
    public function getNamespaces()
    {
        
    }

    /**
     * Finds a registered namespace.
     * 
     * @param  string  $namespace
     * 
     * @return string
     */
    public function findNamespace(string $namespace)
    {

    }

    /**
     * Renders errors for define a verbose quiet.
     * 
     * @param  \Throwable  $e
     * @param  \Syscodes\Components\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return void 
     */
    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {

    }

    /**
     * Renders errors caused by not defining variables, methods and console command classes.
     * 
     * @param  \Throwable  $e
     * @param  \Syscodes\Components\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return void 
     */
    protected function doRenderError(Throwable $e, OutputInterface $output): void
    {

    }

    /**
     * Finds alternative of `$name` among collection.
     * 
     * @param  string  $name
     * @param  iterable  $collection
     * 
     * @return string[]
     */
    public function getCommandAlternatives(string $name, iterable $collection)
    {

    }
    
    /**
     * @inheritdoc
     */
    public function getHelp(): string
    {
        return $this->getConsoleVersion();
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

    /**
     * Initializes the ListCommand and HelpCommand classes.
     * 
     * @return void
     */
    protected function initialize()
    {
        if ($this->initialize) {
            return;
        }

        $this->initialize = true;

        foreach ($this->getDefaultCommands() as $command) {
            $this->addCommand($command);
        }
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