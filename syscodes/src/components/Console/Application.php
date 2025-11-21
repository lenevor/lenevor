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

namespace Syscodes\Components\Console;

use Closure;
use ReflectionClass;
use Syscodes\Components\Version;
use Syscodes\Components\Events\Dispatcher;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Contracts\Console\Application as ApplicationContract;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Console application.
 */
class Application extends SymfonyApplication implements ApplicationContract
{	
	/**
	 * A map of command names to classes.
	 * 
	 * @var array
	 */
	protected $commandMap = [];

	/**
	 * The event dispatcher instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Events\Dispatcher $events
	 */
	protected $events;

	/**
	 * The Lenevor application instance.
	 * 
	 * @var \Syscodes\Components\Contracts\Container|Container $lenevor
	 */
	protected $lenevor;

	/**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;
	
	/**
	 * The console application bootstrappers.
	 * 
	 * @var array<array-key, \Closure($this): void>
	 */
	protected static $bootstrappers = [];

	/**
	 * Console constructor. Initialize the console of Lenevor.
	 *
	 * @param  \Syscodes\Components\Contracts\Container\Container  $lenevor
	 * 
	 * @param  string  $version
	 * 
	 * @return void
	 */
	public function __construct(Container $lenevor, Dispatcher $events, string $version)
	{
		parent::__construct(Version::NAME, $version);

		$this->events  = $events;
		$this->lenevor = $lenevor;
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

		$this->bootstrap();
	}

	/**
	 * Register a console "starting" bootstrapper.
	 * 
	 * @param  \Closure($this): void  $callback
	 * 
	 * @return void
	 */
	public static function starting(Closure $callback): void
	{
		static::$bootstrappers[] = $callback;
	}
	
	/**
	 * Bootstrap the console application.
	 * 
	 * @return void
	 */
	protected function bootstrap(): void
	{
		foreach (static::$bootstrappers as $bootstrapper) {
			$bootstrapper($this);
		}
	}
	
	/**
	 * Add a command, resolving through the application.
	 * 
	 * @param  \Syscodes\Components\Console\Command|string  $command
	 * 
	 * @return \Symfony\Component\Console\Command\Command|null
	 */
	public function resolve($command)
    {
        if (is_subclass_of($command, SymfonyCommand::class)) {
            $attribute = (new ReflectionClass($command))->getAttributes(AsCommand::class);

            $commandName = ! empty($attribute) ? $attribute[0]->newInstance()->name : null;

            if ( ! is_null($commandName)) {
                foreach (explode('|', $commandName) as $name) {
                    $this->commandMap[$name] = $command;
                }
            }
        }

        if ($command instanceof Command) {
            return $this->add($command);
        }

        return $this->add($this->lenevor->make($command));
    }
	
	/**
	 * Resolve an array of commands through the application.
	 * 
	 * @param  mixed  $commands
	 * 
	 * @return static
	 */
	public function resolveCommands($commands): static
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
    }

	/**
     * Run an Artisan console command by name.
     *
     * @param  \Syscodes\Components\Console\Command\Command|string  $command
     * @param  array  $parameters
     * @param  \Syscodes\Components\Console\Output\OutputInterface|null  $outputBuffer
	 * 
     * @return int
     *
     * @throws \Syscodes\Components\Console\Exception\CommandNotFoundException
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        [$command, $input] = $this->parseCommand($command, $parameters);

        if ( ! $this->has($command)) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $command));
        }

        return $this->run(
            $input,  $outputBuffer
        );
    }

	/**
     * Parse the incoming Prime command and its input.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $parameters
     * 
     * @return array
     */
    protected function parseCommand($command, $parameters)
    {
        if (is_subclass_of($command, SymfonyCommand::class)) {
            $callingClass = true;

            if (is_object($command)) {
                $command = get_class($command);
            }

            $command = $this->lenevor->make($command)->getName();
        }

        if (! isset($callingClass) && empty($parameters)) {
            $command = $this->getCommandName(($command));
        } else {
            array_unshift($parameters, $command);

            $input = new ArrayInput($parameters);
        }

        return [$command, $input];
    }
	
	/**
     * Add a command to the console.
     *
     * @param  \Syscodes\Components\Console\Command  $command
	 * 
     * @return \Syscodes\Components\Console\Command|null
     */
    #[\Override]
    public function add(SymfonyCommand $command): ?SymfonyCommand
	{
		return $this->addCommand($command);
	}

     /**
     * Add a command to the console.
     *
     * @param  \Symfony\Component\Console\Command\Command|callable  $command
     * 
     * @return \Symfony\Component\Console\Command\Command|null
     */
    public function addCommand(SymfonyCommand|callable $command): ?SymfonyCommand
    {
        if ($command instanceof Command) {
            $command->setLenevor($this->lenevor);
        }

        return $this->addToParent($command);
    }
	
	/**
     * Add the command to the parent instance.
     *
     * @param  \Symfony\Component\Console\Command\Command  $command
	 * 
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function addToParent(SymfonyCommand $command)
    { 
        if (method_exists(SymfonyApplication::class, 'addCommand')) {
            /** @phpstan-ignore staticMethod.notFound */
            return parent::addCommand($command);
        }

        return parent::add($command);
    }

	/**
	 * Returns the version of the console.
     *
     * @return string
	 */
	public function getLongVersion(): string
	{
		return parent::getLongVersion().
			sprintf(' (env: <info>%s</>, debug: <info>%s</>) [<comment>%s</>]',
				env('APP_ENV'), env('APP_DEBUG') ? 'true' : 'false', PHP_OS
			);
	}

    /**
     * Set the container command loader for lazy resolution.
     *
     * @return static
     */
    public function setResolveCommandLoader(): static
    {
        $this->setCommandLoader(new ResolveCommandLoader($this->lenevor, $this->commandMap));

        return $this;
    }

	/**
     * Get the default input definition for the application.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Syscodes\Components\Console\Input\InputDefinition
     */
    #[\Override]
    protected function getDefaultInputDefinition(): InputDefinition
    {
        return take(parent::getDefaultInputDefinition(), function ($definition) {
            $definition->addOption($this->getEnvironmentOption());
        });
    }

    /**
     * Get the global environment option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption(): InputOption
    {
        $message = 'The environment the command should run under';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

	/**
	 * Gets the Lenevor application instance.
	 * 
	 * @return \Syscodes\Components\Contracts\Core\Application
	 */
	public function getLenevor()
	{
		return $this->lenevor;
	}
}