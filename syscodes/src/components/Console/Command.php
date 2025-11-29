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

use Exception;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Syscodes\Components\Console\View\Components\Factory;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Is class allows functionality for running, listing, etc all commands of framework.
 */
class Command extends SymfonyCommand
{
    use Concerns\CallCommands,
        Concerns\ConfirmProcess,
        Concerns\HasParameters,
        Concerns\InteractsWithIO;

    /**
     * The console command name aliases.
     *
     * @var array
     */
    protected $aliases;

    /**
     * Gets the commands.
     * 
     * @var array
     */
    protected $commands = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The console command help text.
     *
     * @var string
     */
    protected $help = '';
    
    /**
     * The Lenevor appplication instance.
     * 
     * @var \Syscodes\Components\Contracts\Core\Application $lenevor
     */
    protected $lenevor;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name;

    /**
     * The name and signature of the console command.
     * 
     * @var string|null
     */
    protected $signature;

    /**
     * Constructor. Create a new Command instance.
     * 
     * @return void
     */
    public function __construct()
    {
        if (isset($this->signature)) {
            parent::__construct($this->name = $this->signature);
        } else {
            parent::__construct($this->name);
        }

        if ( ! empty($this->description)) {
            $this->setDescription($this->description);
        }

        if ( ! empty($this->help)) {
            $this->setHelp($this->help);
        }

        $this->setHidden($this->isHidden());
        
        if (isset($this->aliases)) {
            $this->setAliases((array) $this->aliases);
        }

        if ( ! isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    /**
     * Runs a command given.
     * 
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return int|mixed
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $this->lenevor->make(
            OutputStyle::class, ['input' => $input, 'output' => $output]
        );

        $this->components = $this->lenevor->make(Factory::class, ['output' => $this->output]);
        
        return parent::run(
            $this->input = $input, $this->output = $output
        );
    }

    /**
     * Executes the current command.
     * 
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return int
     * 
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        try {
            return (int) $this->lenevor->call([$this, $method]);
        } catch (Exception $e) {
            throw $e;

            return static::FAILURE;
        }
    }
    
    /**
     * Resolve the console command instance for the given command.
     * 
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * 
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function resolveCommand($command)
    {
        if ( ! class_exists($command)) {
            return $this->getApplication()->find($command);
        }
        
        $command = $this->lenevor->make($command);
        
        if ($command instanceof SymfonyCommand) {
            $command->setApplication($this->getApplication());
        }
        
        if ($command instanceof self) {
            $command->setLenevor($this->getLenevor());
        }
        
        return $command;
    }

    /**
     * Get the Lenevor application instance.
     * 
     * @return \Syscodes\Components\Contracts\Core\Application
     */
    public function getLenevor()
    {
        return $this->lenevor;
    }

    /**
     * Set the Lenevor application instance.
     * 
     * @param  \Syscodes\Components\Contracts\Core\Application  $lenevor
     * 
     * @return void
     */
    public function setLenevor($lenevor): void
    {
        $this->lenevor = $lenevor;
    }
}