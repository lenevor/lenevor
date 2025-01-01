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

use Throwable;
use TypeError;
use LogicException;
use ReflectionProperty;
use InvalidArgumentException;
use Syscodes\Components\Support\Str;
use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Input\InputDefinition;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Base class for all commands.
 */
class Command 
{
    /**
     * The default application.
     * 
     * @var \Syscodes\Components\Console\Application $application
     */
    protected $application;

    /**
     * The default command name.
     * 
     * @var string|null $defaultName
     */
    protected static $defaultName;

     /**
     * The default command description.
     * 
     * @var string|null $defaultDescription
     */
    protected static $defaultDescription;

    /**
     * Gets the aliases of command name.
     * 
     * @var string[] $aliases
     */
    protected $aliases = [];

    /**
     * Gets the Command's Arguments description.
     * 
     * @var array $arguments
     */
    protected $arguments = [];

    /**
     * The code to execute some command.
     * 
     * @var int $code
     */
    protected $code = 0;

    /**
     * The InputDefinition implement.
     * 
     * @var \Syscodes\Components\Console\Input\InputDefinition $definition
     */
    protected $definition;

    /**
     * The console command description.
     * 
     * @var string|null $description
     */
    protected $description;

    /**
     * The InputDefinition full implemented.
     * 
     * @var \Syscodes\Components\Console\Input\InputDefinition $fullDefinition
     */
    protected $fullDefinition;

    /**
     * The group of commands is lumped under, when listing commands.
     * 
     * @var string $group
     */
    protected $group;

    /**
     * The console command help text.
     * 
     * @var string $help
     */
    protected $help;

    /**
     * Indicates whether the command should be shown in the Prime command list.
     * 
     * @var bool $hidden
     */
    protected $hidden = false;

    /**
     * The validation of ignored errors 
     * 
     * @var bool $ignoreValidationErrors
     */
    protected $ignoreValidationErrors = false;

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
     * The console command synopsis.
     * 
     * @var array $synopsis
     */
    protected $synopsis = [];

    /**
     * The console command usages.
     * 
     * @var array $usages
     */
    protected $usages = [];

    /**
     * Gets the default name.
     * 
     * @return string|null
     */
    public static function getDefaultName(): ?string
    {
        $class = static::class;

        $property = new ReflectionProperty($class, 'defaultName');

        return ($class === $property->class) ? static::$defaultName : null;
    }

    /**
     * Gets the default description.
     * 
     * @return string|null
     */
    public static function getDefaultDescription(): ?string
    {
        $class = static::class;

        $property = new ReflectionProperty($class, 'default description');

        return ($class === $property->class) ? static::$defaultDescription : null;
    }

    /**
     * Constructor. Create a new base command instance.
     * 
     * @param  string|null  $name  The name command
     * @param  \Syscodes\Components\Console\Input\InputDefinition  $definition
     * 
     * @return void
     */
    public function __construct(?string $name = null)
    {
        $this->definition = new InputDefinition();
        
        if ($name && null !== $name = static::getDefaultName()) {
            $aliases = explode('|', $name);
            
            if ('' === $name = array_shift($aliases)) {
                $this->setHidden(true);
                
                $name = array_shift($aliases);
            }
            
            $this->setAliases($aliases);
        }
        
        if (null !== $name) {
            $this->setName($name);
        }
        
        if ('' === $this->description) {
            $this->setDescription(static::getDefaultDescription() ?? '');
        }
        
        $this->define();
    }

    /**
     * Gets input definition for command.
     * 
     * @return void
     */
    protected function define() {}

    /**
     * Sets the command.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * 
     * @return void
     */
    public function setCommand(Command $command) {}

    /**
     * Executes the current command.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
     * 
     * @return int|mixed
     * 
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output) {}

    /**
     * Runs the command.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
     * 
     * @return int|mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // add the application arguments and options
        $this->mergeApplicationDefinition();

        try {
            $input->linked($this->getDefinition());
        } catch (Throwable $e) {
            if ( ! $this->ignoreValidationErrors) {
                throw $e;
            }
        }
        
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }

        $statusCode = '';

        if (0 === (int) $this->code) {
            $statusCode = $this->execute($input, $output);
        } else {
            throw new TypeError(
                sprintf('Returned value in "%s::execute()" must be of the type int, "%s" returned', static::class, get_debug_type($statusCode))
            );
        }

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }
    
    /**
     * Merges the application definition with the command definition.
     * 
     * @param  bool  $mergeArgs  Whether to merge or not the Application definition arguments to Command definition arguments
     * 
     * @internal
     */
    public function mergeApplicationDefinition(bool $mergeArgs = true)
    {
        if (null === $this->application) {
            return;
        }
        
        $this->fullDefinition = new InputDefinition();
        $this->fullDefinition->setOptions($this->definition->getOptions());
        $this->fullDefinition->addOptions($this->application->getDefinition()->getOptions());
        
        if ($mergeArgs) {
            $this->fullDefinition->setArguments($this->application->getDefinition()->getArguments());
            $this->fullDefinition->addArguments($this->definition->getArguments());
        } else {
            $this->fullDefinition->setArguments($this->definition->getArguments());
        }
    }

    /**
     * Gets the InputDefinition to be used to representate arguments 
     * and options in a command.
     * 
     * @return \Syscodes\Components\Console\Input\InputDefinition
     */
    public function getDefinition()
    {
        if (null === $this->definition) {
            throw new LogicException(
                sprintf('Probably Command class "%s" is not correctly initialized  because forget to call the parent constructor', static::class)
            );
        }

        return $this->fullDefinition ?? $this->definition;
    }
    
    /**
     * Sets the InputDefinition to be used to representate arguments
     * and options in a command.
     * 
     * @param  array|\Syscodes\Components\Console\Input\InputDefinition  $definition  An array of InputArgument and InputOption instance
     * 
     * @return static
     */
    public function setDefinition($definition): static 
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
        
        $this->fullDefinition = null;

        return $this;
    }

    /**
     * Adds an argument in a command.
     * 
     * @param  string  $name  The argument name
     * @param  int|null  $mode  The argument mode
     * @param  string  $description  The description text
     * @param  mixed|null  $default  The default value
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException  When argument mode is not valid
     * @throws \LogicException
     */
    public function addArgument(
        string $name,
        ?int $mode = null,
        string $description = '',
        mixed $default = null
    ): static {
        $this->definition->addArgument(new InputArgument($name, $mode, $description, $default));

        return $this;
    }

    /**
     * Adds an option in a command.
     * 
     * @param  string  $name  The argument name
     * @param  string|array|null  $shortcut  The shortcut of the option
     * @param  int|null  $mode  The argument mode
     * @param  string  $description  The description text
     * @param  mixed  $default  The default value
     * 
     * @return static
     * 
     * @throws \InvalidArgumentException  If option mode is invalid
     */
    public function addOption(
        string $name, 
        $shortcut = null,
        ?int $mode = null,
        string $description = '',
        $default = null
    ): static {
        $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));

        return $this;
    }
    
    /**
     * Gets the command name.
     * 
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the name of the command.
     * 
     * @param  string  $name  The command name
     * 
     * @return static
     */
    public function setName(string $name): static
    {
        $this->validateName($name);

        $this->name = $name;

        return $this;
    }

    /**
     * Gets the application instance for this command.
     * 
     * @return \Syscodes\Components\Console\application|null
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * sets the application instance for this command.
     * 
     * @return static
     */
    public function setApplication(?Application $application = null): static
    {
       $this->application = $application;

       return $this;
    }

    /**
     * Gets the code when execute a command.
     * 
     * @return int
     */
    public function getcode(): int
    {
        return $this->code;
    }

    /**
     * Sets the code when execute a command.
     * 
     * @param  int  $code  The code to execute in the command
     * 
     * @return static
     */
    public function setcode(int $code): static
    {
        $this->code = $code;

        return $this;
    }
    
    /**
     * Gets whether the command should be publicly shown or not.
     * 
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }
    
    /**
     * Whether or not the command should be hidden from the list of commands.
     * 
     * @param  bool  $hidden
     * 
     * @return static
     */
    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;
        
        return $this;
    }
    
    /**
     * Returns the description for the command.
     * 
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    /**
     * Sets the description for the command.
     * 
     * @param  string  $description The command description
     * 
     * @return static
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * Returns the help for the command.
     *
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * Returns the proccesed help for the command.
     * 
     * @return string
     */
    public function getProccesedHelp(): string
    {
        $name = $this->getName();

        $isSingleCommand = $this->application && $this->application->isSingleCommand();

        $placeholder = [
            '%command-name%',
            '%command-fullname%',
        ];

        $replacement = [
            $name,
            $isSingleCommand ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'].' '.$name,
        ];

        return str_replace($placeholder, $replacement, $this->getHelp() ?: $this->getDescription());
    }

    /**
     * Sets the help for the command.
     *
     * @return static
     */
    public function setHelp(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    /**
     * Returns alternative usages of the command.
     * 
     * @return array
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * Add a command usage as example.
     * 
     * @param  string  $usage  The command name usage
     * 
     * @return static
     */
    public function addUsage(string $usage): static
    {
        if ( ! Str::startsWith($usage, $this->name)) {
            $usage = sprintf('%s %s', $this->name, $usage);
        }

        $this->usages[] = $usage;

        return $this;
    }

    /**
     * Returns the synopsis for the command.
     * 
     * @param  bool  short
     * 
     * @return string
     */
    public function getSynopsis(bool $short = false): string
    {
        $value = $short ? 'short' : 'long';

        if ( ! isset($this->synopsis[$value])) {
            $this->synopsis[$value] = sprintf('%s %s', $this->name, $this->definition->getSynopsis($short));
        }

        return $this->synopsis[$value];
    }

    /**
     * Gets the aliases for the command.
     * 
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
    
    /**
     * Sets the aliases for the command.
     * 
     * @param string[] $aliases An array of aliases for the command
     * 
     * @return static
     * 
     * @throws InvalidArgumentException When an alias is invalid
     */
    public function setAliases(iterable $aliases): static
    {
        $list = [];
        
        foreach ($aliases as $alias) {
            $this->validateName($alias);
            $list[] = $alias;
        }
        
        $this->aliases = is_array($aliases) ? $aliases : $list;
        
        return $this;
    }
    
    /**
     * Checks whether the command is enabled or not in the current environment.
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * Validates a command name. e.g: php prime make:example.
     * 
     * @param  string  $name
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    private function validateName(string $name): void
    {
        if ( ! preg_match('/^[^\:]++(\:[^\:]++)*$/', $name)) {
            throw new InvalidArgumentException(sprintf('Command name "%s" is invalid', $name));
        }
    }
}