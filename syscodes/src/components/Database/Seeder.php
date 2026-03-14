<?php

/**
 * Lenevor PHP Framework
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
 * @copyright   Copyright (c) 2019 - 2026 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */
 
namespace Syscodes\Components\Database;

use InvalidArgumentException;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Console\View\Components\TwoColumnDetail;
use Syscodes\Components\Contracts\Container\Container;
use Syscodes\Components\Database\Console\Seeds\WithoutModelEvents;
use Syscodes\Components\Support\Arr;

/**
 * Allows run towards in the database seeds.
 */
abstract class Seeder
{
    /**
     * Seeders that have been called at least one time.
     *
     * @var array
     */
    protected static $called = [];
        
    /**
     * The console command instance.
     *
     * @var \Syscodes\Components\Console\Command
     */    
    protected $command;

    /**
     * The container instance.
     *
     * @var \Syscodes\Components\Contracts\Container\Container
     */
    protected $container;
    
    /**
     * Run the given seeder class.
     *
     * @param  array|string  $class
     * @param  bool  $silent
     * @param  array  $parameters
     * 
     * @return static
     */
    public function call($class, $silent = false, array $parameters = []): static
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            $seeder = $this->resolve($class);

            $name = get_class($seeder);

            if ($silent === false && isset($this->command)) {
                (new TwoColumnDetail($this->command->getOutput()))
                    ->render($name, '<fg=yellow;options=bold>RUNNING</>');
            }

            $startTime = microtime(true);

            $seeder->__invoke($parameters);

            if ($silent === false && isset($this->command)) {
                $runTime = number_format((microtime(true) - $startTime) * 1000);

                (new TwoColumnDetail($this->command->getOutput()))
                    ->render($name, "<fg=gray>$runTime ms</> <fg=green;options=bold>DONE</>");

                $this->command->getOutput()->writeln('');
            }

            static::$called[] = $class;
        }

        return $this;
    }

    /**
     * Run the given seeder class.
     *
     * @param  array|string  $class
     * @param  array  $parameters
     * 
     * @return void
     */
    public function callWith($class, array $parameters = [])
    {
        $this->call($class, false, $parameters);
    }

    /**
     * Silently run the given seeder class.
     *
     * @param  array|string  $class
     * @param  array  $parameters
     * 
     * @return void
     */
    public function callSilent($class, array $parameters = [])
    {
        $this->call($class, true, $parameters);
    }

    /**
     * Run the given seeder class once.
     *
     * @param  array|string  $class
     * @param  bool  $silent
     * @param  array  $parameters
     * 
     * @return void
     */
    public function callOnce($class, $silent = false, array $parameters = [])
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            if (in_array($class, static::$called)) {
                continue;
            }

            $this->call($class, $silent, $parameters);
        }
    }

    /**
     * Resolve an instance of the given seeder class.
     *
     * @param  string  $class
     * 
     * @return \Syscodes\Components\Database\Seeder
     */
    protected function resolve($class): self
    {
        if (isset($this->container)) {
            $instance = $this->container->make($class);

            $instance->setContainer($this->container);
        } else {
            $instance = new $class;
        }

        if (isset($this->command)) {
            $instance->setCommand($this->command);
        }

        return $instance;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Syscodes\components\Contracts\Container\Container  $container

     * @return static
     */
    public function setContainer(Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set the console command instance.
     *
     * @param  \Syscodes\components\Console\Command  $command
     * 
     * @return static
     */
    public function setCommand(Command $command): static
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Run the database seeds.
     *
     * @param  array  $parameters
     * 
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke(array $parameters = [])
    {
        if ( ! method_exists($this, 'run')) {
            throw new InvalidArgumentException('Method [run] missing from '.get_classname($this, true));
        }

        $callback = fn () => isset($this->container)
            ? $this->container->call([$this, 'run'], $parameters)
            : $this->run(...$parameters);

        $uses = array_flip(class_recursive(static::class));

        if (isset($uses[WithoutModelEvents::class])) {
            $callback = $this->withoutModelEvents($callback);
        }

        return $callback();
    }
}