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
 * @copyright   Copyright (c) 2019 - 2024 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Concerns;

use Syscodes\Components\Support\Collection;
use Syscodes\Components\Contracts\Console\PromptsForMissingInput as PromptsForMissinginputContract;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait PromptsForMissingInput.
 */
trait PromptsForMissingInput
{
    /**
     * Interact with the user before validating the input.
     * 
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
        
        if ($this instanceof PromptsForMissinginputContract) {
            $this->promptForMissingArguments($input, $output);
        }
    }

     /**
     * Prompt the user for any missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return void
     */
    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output):void
    {
        $prompted = (new Collection($this->getDefinition()->getArguments()))
            ->reject(fn (InputArgument $argument) => $argument->getName() === 'command')
            ->filter(fn (InputArgument $argument) => $argument->isRequired() && match (true) {
                $argument->isArray() => empty($input->getArgument($argument->getName())),
                default => is_null($input->getArgument($argument->getName())),
            })
            ->each(fn (InputArgument $argument) => $input->setArgument(
                $argument->getName(),
                $this->askPrompt(
                    $this->promptForMissingArgumentsUsing()[$argument->getName()] ??
                         'What is '.lcfirst($argument->getDescription() ?: ('the '.$argument->getName())).'?',
                    $argument
                )
            ))
            ->isNotEmpty();

        if ($prompted) {
            $this->afterPromptingForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        //
    }

    /**
     * Continue asking a question until an answer is provided.
     *
     * @param  string  $question
     * 
     * @return string
     */
    protected function askPrompt($question, $argument): string
    {
        $answer = null;

        while ($answer === null) {
            $answer = $this->components->ask($question);

            if ($answer === null) {
                $this->components->error("The {$argument->getName()} is required.");
            }
        }

        return $answer;
    }
}