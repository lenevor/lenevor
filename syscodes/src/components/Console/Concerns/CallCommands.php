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

namespace Syscodes\Components\Console\Concerns;

use Syscodes\Components\Support\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Calls the console command.
 */
trait CallCommands
{
    /**
     * Resolve the console command instance for the given command.
     * 
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * 
     * @return \Symfony\Component\Console\Command\Command
     */
    abstract protected function resolveCommand($command);
    
    /**
     * Call another console command.
     * 
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
     * 
     * @return int
     */
    public function call($command, array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, $this->output);
    }
    
    /**
     * Call another console command without output.
     * 
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
     * 
     * @return int
     */
    public function callSilent($command, array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, new NullOutput);
    }
    
    /**
     * Run the given the console command.
     * 
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return int
     */
    protected function runCommand($command, array $arguments, OutputInterface $output): int
    {
        $arguments['command'] = $command;
        
        return $this->resolveCommand($command)->run(
            $this->createInputFromArguments($arguments), $output
        );
    }
    
    /**
     * Create an input instance from the given arguments.
     * 
     * @param  array  $arguments
     * 
     * @return \Symfony\Component\Console\Input\ArrayInput
     */
    protected function createInputFromArguments(array $arguments)
    {
        return take(new ArrayInput(array_merge($this->context(), $arguments)), function ($input) {
            if ($input->getParameterOption('--no-interaction')) {
                $input->setInteractive(false);
            }
        });
    }
    
    /**
     * Get all of the context passed to the command.
     * 
     * @return array
     */
    protected function context(): array
    {
        return (new Collection($this->option()))
            ->only([
                'ansi',
                'no-ansi',
                'no-interaction',
                'quiet',
                'verbose',
            ])
            ->filter()
            ->mapKeys(fn ($value, $key) => ["--{$key}" => $value])
            ->all();
    }
}