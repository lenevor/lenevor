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

namespace Syscodes\Console\Command;

use ReflectionProperty;
use ReflectionException;
use InvalidArgumentException;
use Syscodes\Contracts\Console\InputDefinition;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * Class BaseCommand.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
abstract class BaseCommand 
{
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
     * The InputDefinition implement.
     * 
     * @var \Syscodes\Console\Input\InputDefinition $definition
     */
    protected $definition;

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
     * Gets the default name.
     * 
     * @return string|null
     */
    public static function getDefaultName()
    {
        $class = static::class;

        $property = new ReflectionProperty($class, 'default name');

        return ($class === $property->class) ? static::$defaultName : null;
    }

    /**
     * Gets the default description.
     * 
     * @return string|null
     */
    public static function getDefaultDescription()
    {
        $class = static::class;

        $property = new ReflectionProperty($class, 'default description');

        return ($class === $property->class) ? static::$defaultDescription : null;
    }

    /**
     * Constructor. Create a new base command instance.
     * 
     * @param  string|null  $name  The name command
     * @param  \Syscodes\Console\Input\InputDefinition  $definition
     * 
     * @return void
     */
    public function __construct(string $name = null, InputDefinition $definition = null)
    {
        $this->definition = (null === $definition) ? new InputDefinition() : $definition;
        
        if (null === $name && null !== $name = static::getDefaultName()) {
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
        
    }

    /**
     * Configure input definition for command.
     * 
     * @return void
     */
    abstract protected function configure(): void;

    /**
     * Executes the current command.
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    abstract protected function execute(): void;

    /**
     * Runs the command.
     * 
     * @return int|mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->configure();


    }

    /**
     * Gets the command name.
     * 
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the command.
     * 
     * @param  string  $name  The command name
     * 
     * @return self
     */
    public function setName(string $name): self
    {
        $this->validateName($name);

        $this->name = $name;

        return $this;
    }
    
    /**
     * Gets whether the command should be publicly shown or not.
     * 
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }
    
    /**
     * Whether or not the command should be hidden from the list of commands.
     * 
     * @param  bool  $hidden
     * 
     * @return self
     */
    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;
        
        return $this;
    }
    
    /**
     * Returns the description for the command.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Sets the description for the command.
     * 
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * Sets the help for the command.
     *
     * @return $this
     */
    public function setHelp(string $help): self
    {
        $this->help = $help;

        return $this;
    }

    /**
     * Returns the help for the command.
     *
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Gets the aliases for the command.
     * 
     * @return string[]
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Validates a command name. e.g: php prime make:example.
     * 
     * @param  string  $name
     * 
     * @return bool
     * 
     * @throws \InvalidArgumentException
     */
    private function validateName(string $name)
    {
        if ( ! \preg_match('/^[^\:]++(\:[^\:]++)*$/', $name)) {
            throw new InvalidArgumentException(\sprintf('Command name "%s" is invalid', $name));
        }
    }
}