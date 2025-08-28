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

namespace Syscodes\Components\Console\Command;

use Exception;
use Throwable;
use LogicException;
use Syscodes\Components\Console\GlobalOption;
use Syscodes\Components\Console\IO\Interactor;
use Syscodes\Components\Console\Input\ArgvInput;
use Syscodes\Components\Console\Input\ArrayInput;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Command\HelpCommand;
use Syscodes\Components\Console\Command\ListCommand;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Output\ConsoleOutput;
use Syscodes\Components\Console\Input\InputDefinition;
use Syscodes\Components\Core\Console\Commands\AboutCommand;
use Syscodes\Components\Console\Concerns\BuildConsoleVersion;
use Syscodes\Components\Console\Exceptions\CommandNotFoundException;
use Syscodes\Components\Console\Exceptions\NamespaceNotFoundException;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Application as ApplicationContract;
use Syscodes\Components\Contracts\Console\Input\InputOption as InputOptionInterface;
use Syscodes\Components\Contracts\Console\Input\InputArgument as InputArgumentInterface;

/**
 * This is the main entry point of a Console application.
 * 
 * This class is optimized for a standard CLI environment.
 */
class Application implements ApplicationContract
{
    use BuildConsoleVersion;

    /** Gets the auto exit.
     * 
     * @var bool $autoExit
     */
    protected bool $autoExit = true;

    /**
     * Gets the command name.
     * 
     * @var array $commands
     */
    protected $commands = [];

    /**
     * Get the command name loader.
     * 
     * @var mixed $commandLoader
     */
    protected $commandLoader = null;
    
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

    /**
     * The running command.
     * 
     * @var object|string|null
     */
    protected $runningCommand = null;

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
     * Gets whether to automatically exit after a command execution or not.
     * 
     * @return bool
     */
    public function isAutoExitEnabled(): bool
    {
        return $this->autoExit;
    }
    
    /**
     * Sets whether to automatically exit after a command execution or not.
     * 
     * @param  bool  $value
     * 
     * @return void
     */
    public function setAutoExit(bool $value): void
    {
        $this->autoExit = $value;
    }
    
    /**
     * Runs the current command discovered on the CLI.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input|null  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output\Output|null  $output  The output interface implemented
     * 
     * @return int
     */
    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
    {
        $input ??= new ArgvInput();
        $output ??= new ConsoleOutput();
        
        $this->configureIO($input, $output);
        
        try {
            $exitCode = $this->doExecute($input, $output);
        } catch (Exception $e) {
            throw $e;
            
            $exitCode = $e->getCode();
            
            if (is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                
                if ($exitCode <= 0) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        }
        
        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }
            
            exit($exitCode);
        }
        
        return $exitCode;
    }
    
    /**
     * Configures the input and output instances.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return \Syscodes\Components\Console\IO\Interactor
     */
    protected function configureIO($input, $output)
    {
        return (new Interactor($input, $output))->getConfigureIO();
    }
    
    /**
     * Executes the current application of console.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input  The input interface implemented
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return int
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
            $this->runningCommand = null;
            $command = $this->findCommand($name);
        } catch(Throwable $e) {
            if ( ! ($e instanceof CommandNotFoundException && ! $e instanceof NamespaceNotFoundException) || 1 !== \count($alternatives = $e->getAlternatives()) || ! $input->isInteractive()) {
                throw $e;
            }

            $alternative = $alternatives[0];

            $output->write(sprintf('Command "%s" not defined', $name));

            $command = $this->findCommand($alternative);
        }
        
        $this->runningCommand = $command;
        $exitCode = $this->doCommand($command, $input, $output);
        $this->runningCommand = null;

        return $exitCode;
    }
    
    /**
     * Registers a new command.
     * 
     * @param  string  $name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     */
    public function register(string $name): Command
    {
        return $this->addCommand(new Command($name));
    }

    /**
     * Adds a command object.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * 
     * @return \Syscodes\Components\Console\Command\Command|null
     * 
     * @throws \LogicException
     */
    public function addCommand(Command $command): ?Command
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
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * 
     * @return string|null
     */
    protected function getCommandName(InputInterface $input)
    {
        return $this->singleCommand ? $this->defaultCommand : $input->getFirstArgument();
    }

    /**
     * Gets input definition.
     * 
     * @return \Syscodes\Components\Console\Input\InputDefinition
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
     * {@internal}
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
            new InputOption('--ansi', '', InputOptionInterface::VALUE_NEGATABLE, 'Force (or disable --no-color) ANSI output', null),
            new InputOption('--no-interaction', '-n', InputOptionInterface::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }

    /**
     * Gets a registered command.
     * 
     * @param  string  $name  The command name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     * 
     * @throws \Syscodes\Components\Console\Exceptions\CommandNotFoundException
     */
    public function get(string $name): Command 
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
     * Returns true if the command exists, false otherwise.
     * 
     * @param  string  $name  The command name
     * 
     * @return bool
     */
    public function has(string $name): bool
    {
        $this->initialize();

        return isset($this->commands[$name]) || ($this->commandLoader?->has($name) && $this->addCommand($this->commandLoader->get($name)));
    }

    /**
     * Runs the current command.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command  The command name
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input  The input interface implemented
	 * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output  The output interface implemented
     * 
     * @return int  0 if everything went fine, or an error code
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
     * Finds a registered namespace.
     * 
     * @param  string  $namespace
     * 
     * @return string
     */
    public function findNamespace(string $namespace)
    {
        $allNamespaces = $this->getNamespaces();
        
        $expr = implode('[^:]*:', array_map('preg_quote', explode(':', $namespace))).'[^:]*';
        
        $namespaces = preg_grep('{^'.$expr.'}', $allNamespaces);
        
        if ( ! $namespaces) {
            $message = sprintf('There are no commands defined in the "%s" namespace.', $namespace);
            
            if ($alternatives = $this->findCommandAlternatives($namespace, $allNamespaces)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                
                $message .= implode("\n    ", $alternatives);
            }
            
            throw new NamespaceNotFoundException($message, $alternatives);
        }
        
        $exact = in_array($namespace, $namespaces, true);
        
        if (count($namespaces) > 1 && ! $exact) {
            throw new NamespaceNotFoundException(sprintf("The namespace \"%s\" is ambiguous.\nDid you mean one of these?\n%s", $namespace, $this->getAbbreviationSuggestions(array_values($namespaces))), array_values($namespaces));
        }
        
        return $exact ? $namespace : reset($namespaces);
    }

    /**
     * Gets all the namespaces used by currently registered commands.
     * 
     * @return string[]
     */
    public function getNamespaces(): array
    {
        $namespaces = [];
        
        foreach ($this->all() as $command) {
            if ($command->isHidden()) {
                continue;
            }
            
            $namespaces[] = $this->extractAllNamespaces($command->getName());
            
            foreach ($command->getAliases() as $alias) {
                $namespaces[] = $this->extractAllNamespaces($alias);
            }
        }
        
        return array_values(array_unique(array_filter(array_merge([], ...$namespaces))));        
    }

    /**
     * Finds a command by name.
     * 
     * @param  string  $name  The command name
     * 
     * @return \Syscodes\Components\Console\Command\Command
     * 
     * @throws \Syscodes\Components\Console\Exceptions\CommandNotFoundException
     */
    public function findCommand(string $name): Command
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

        if ( ! $commands) {
            $commands = preg_grep('{^'.$expression.'}i', $allCommands);
        }
        
        if ( ! $commands || count(preg_grep('{^'.$expression.'$}i', $commands)) < 1) {
            if (false !== $pos = strrpos($name, ':')) {
                // check if a namespace exists and contains commands
                $this->findNamespace(substr($name, 0, $pos));
            }
            
            $message = sprintf('Command "%s" is not defined.', $name);
            
            if ($alternatives = $this->findCommandAlternatives($name, $allCommands)) {
                // remove hidden commands
                $alternatives = array_filter($alternatives, function ($name) {
                    return ! $this->get($name)->isHidden();
                });
                
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                
                $message .= implode("\n    ", $alternatives);
            }
            
            throw new CommandNotFoundException($message, array_values($alternatives));
        }
        
        // filter out aliases for commands which are already on the list
        if (\count($commands) > 1) {
            $commandList = $this->commandLoader ? array_merge(array_flip($this->commandLoader->getNames()), $this->commands) : $this->commands;
            $commands = array_unique(array_filter($commands, function ($nameOrAlias) use (&$commandList, $commands, &$aliases) {
                if ( ! $commandList[$nameOrAlias] instanceof Command) {
                    $commandList[$nameOrAlias] = $this->commandLoader->get($nameOrAlias);
                }
                
                $commandName = $commandList[$nameOrAlias]->getName();
                $aliases[$nameOrAlias] = $commandName;
                
                return $commandName === $nameOrAlias || ! \in_array($commandName, $commands, true);
            }));
        }

        $command = $this->get(headItem($commands));
        
        if ($command->isHidden()) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist', $name));
        }

        return $command;
    }

    /**
     * Gets the commands (registered in the given namespace if provided).
     *
     * @param  string|null  $namespace
     *
     * @return Command[]
     */
    public function all(?string $namespace = null): array
    {
        $this->initialize();

        if (null === $namespace) {
            if ( ! $this->commandLoader) {
                return $this->commands;
            }

            $commands = $this->commands;
            foreach ($this->commandLoader->getNames() as $name) {
                if (!isset($commands[$name]) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }

            return $commands;
        }

        $commands = [];

        foreach ($this->commands as $name => $command) {
            if ($namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1)) {
                $commands[$name] = $command;
            }
        }

        if ($this->commandLoader) {
            foreach ($this->commandLoader->getNames() as $name) {
                if ( ! isset($commands[$name]) && $namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }
        }

        return $commands;
    }
    
    /**
     * Returns an array of possible abbreviations given a set of names.
     * 
     * @return string[]
     */
    public static function getAbbreviations(array $names): array
    {
        $abbrevs = [];
        
        foreach ($names as $name) {
            for ($len = strlen($name); $len > 0; --$len) {
                $abbrev = substr($name, 0, $len);
                $abbrevs[$abbrev][] = $name;
            }
        }
        
        return $abbrevs;
    }
    
    /**
     * Returns abbreviated suggestions in string format.
     * 
     * @param  array  $abbrevs
     * 
     * @return string
     */
    private function getAbbreviationSuggestions(array $abbrevs): string
    {
        return '    '.implode("\n    ", $abbrevs);
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
    public function findCommandAlternatives(string $name, iterable $collection): array
    {
        $threshold    = 1e3;
        $alternatives = [];
        $collections  = [];

        foreach ($collection as $item) {
            $collections[$item] = explode(':', $item);
        }

        foreach (explode(':', $name) as $key => $subname) {
            foreach ($collections as $collectionName => $parts) {
                $exists = isset($alternatives[$collectionName]);
                
                if ( ! isset($parts[$key]) && $exists) {
                    $alternatives[$collectionName] += $threshold;
                    continue;
                } elseif ( ! isset($parts[$key])) {
                    continue;
                }
                
                $levenshtein = levenshtein($subname, $parts[$key]);
                
                if ($levenshtein <= strlen($subname) / 3 || '' !== $subname && str_contains($parts[$key], $subname)) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $levenshtein : $levenshtein;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }
        
        foreach ($collection as $item) {
            $levenshetin = levenshtein($name, $item);
            
            if ($levenshetin <= strlen($name) / 3 || str_contains($item, $name)) {
                $alternatives[$item] = isset($alternatives[$item]) ? $alternatives[$item] - $levenshetin : $levenshetin;
            }
        }
        
        $alternatives = array_filter($alternatives, function ($levenhtein) use ($threshold) { 
            return $levenhtein < 2 * $threshold; 
        });
        
        ksort($alternatives, SORT_NATURAL | SORT_FLAG_CASE);
        
        return array_keys($alternatives);
    }
    
    /**
     * Returns the namespace part of the command name.
     * 
     * @param  string  $name
     * @param  int|null  $limit
     * 
     * @return string
     */
    public function extractNamespace(string $name, ?int $limit = null): string
    {
        $parts = explode(':', $name, -1);
        
        return implode(':', null === $limit ? $parts : array_slice($parts, 0, $limit));
    }
    
    /**
     * Returns all namespaces of the command name.
     * 
     * @return string[]
     */
    private function extractAllNamespaces(string $name): array
    {
        // -1 as third argument is needed to skip the command short name when exploding
        $parts = explode(':', $name, -1);
        $namespaces = [];
        
        foreach ($parts as $part) {
            if (count($namespaces)) {
                $namespaces[] = end($namespaces).':'.$part;
            } else {
                $namespaces[] = $part;
            }
        }
        
        return $namespaces;
    }
    
    /**
     * Gets the help message.
     * 
     * @return string
     */
    public function getHelp(): string
    {
        return $this->getConsoleVersion();
    }

    /**
     * Initializes the ListCommand and HelpCommand classes.
     * 
     * @return void
     */
    protected function initialize(): void
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

    /**
     * Sets the default commands.
     * 
     * @param  string  $commandName
     * @param  bool  $isSingleCommand
     * 
     * @return static
     */
    public function setDefaultCommand(string $commandName, bool $isSingleCommand = false): static
    {
        $this->defaultCommand = explode('|', ltrim($commandName, '|'))[0];

        if ($isSingleCommand) {
            $this->findCommand($commandName);

            $this->singleCommand = true;
        }

        return $this;
    }
}