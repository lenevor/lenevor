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

namespace Syscodes\Components\Console\IO;

use Syscodes\Components\Console\GlobalOption;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * CLI Interactor.
 */
class Interactor
{
    /**
     * The Input instance.
     * 
     * @var \Syscodes\Components\Contracts\Console\Input\Input $input
     */
    protected $input;
    
    /**
     * The Output instance.
     * 
     * @var \Syscodes\Components\Contracts\Console\Output\Output $output
     */
    protected $output;

    /**
     * Constructor. Create a new Interactor instance.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input|null  $input  The input interface implemented
	 * @param  \Syscodes\Components\Contracts\Console\Output\Output|null  $output  The output interface implemented  
     * 
     * @return void
     */
    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null)
    {
        $this->input  = $input;
        $this->output = $output;
    }

    /**
     * Configures the input and output instances based on 
     * the user arguments and options.
     * 
     * @return void
     */
    public function getConfigureIO(): void
    {
        if (true === $this->input->hasParameterOption(['--ansi'], true)) {
            // Activate the color tag if exist is a style applied 
            $this->output->setDecorated(true);
        } elseif (true === $this->input->hasParameterOption(['--no-ansi'], true)) {
            // Deactivates the color tag if exist is a style applied
            $this->output->setDecorated(false);
        }

        if (true === $this->input->hasParameterOption(['--no-interaction', '-n'], true)) {
            $this->input->setInteractive(false);
        }
        
        match ($shellVerbosity = (int) getenv('SHELL_VERBOSITY')) {
            -1 => $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET),
            1 => $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE),
            2 => $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE),
            3 => $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG),
            default => $shellVerbosity = 0,
        };
        
        if (true === $this->input->hasParameterOption(GlobalOption::QUIET_OPTION, true)) {
            $this->output->write('<comment> Ok. ByeBye! </comment>', true);
            $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $shellVerbosity = -1;
        } else {
            if ($this->input->hasParameterOption('-vvv', true) || $this->input->hasParameterOption('--verbose=3', true) || 3 === $this->input->getParameterOption('--verbose', false, true)) {
                $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                $shellVerbosity = 3;
            } elseif ($this->input->hasParameterOption('-vv', true) || $this->input->hasParameterOption('--verbose=2', true) || 2 === $this->input->getParameterOption('--verbose', false, true)) {
                $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                $shellVerbosity = 2;
            } elseif ($this->input->hasParameterOption('-v', true) || $this->input->hasParameterOption('--verbose=1', true) || $this->input->hasParameterOption('--verbose', true) || $this->input->getParameterOption('--verbose', false, true)) {
                $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $shellVerbosity = 1;
            }
        }
        
        if (-1 === $shellVerbosity) {
            $this->input->setInteractive(false);
        }
        
        if (function_exists('putenv')) {
            @putenv('SHELL_VERBOSITY='.$shellVerbosity);
        }
        
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }
}