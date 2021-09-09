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

namespace Syscodes\Console\IO;

use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * CLI Interactor.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class Interactor
{
    /**
     * The Input instance.
     * 
     * @var \Syscodes\Contracts\Console\Input $input
     */
    protected $input;

   /**
     * The Output instance.
     * 
     * @var \Syscodes\Contracts\Console\Output $output
     */
    protected $output;

    /**
     * Constructor. Create a new Interactor instance.
     * 
     * @param  \Syscodes\Contracts\Console\Input|null  $input  The input interface implemented
	 * @param  \Syscodes\Contracts\Console\Output|null  $output  The output interface implemented  
     * 
     * @return void
     */
    public function __construct(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->input  = $input;
        $this->output = $output;
    }

    /**
     * Configures the input and output instances based on 
     * the user arguments and options.
     * 
     * @return mixed
     */
    protected function getConfigureIO()
    {
        if (true === $this->input->hasParameterOption(['--ansi'], true)) {
            $this->output->setDecorated(true);
        } elseif (true === $this->input->hasParameterOption(['--no-ansi'], true)) {
            $this->output->setDecorated(false);
        }
        
        switch ($shellVerbosity = (int) getenv('SHELL_VERBOSITY')) {
            case -1: $this->output->setVerbosity(OutputInterface::VERBOSITY_QUIET); break;
            case  1: $this->output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE); break;
            case  2: $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE); break;
            case  3: $this->output->setVerbosity(OutputInterface::VERBOSITY_DEBUG); break;
            default: $shellVerbosity = 0; break;
        }
        
        if (true === $this->input->hasParameterOption(['--quiet', '-q'], true)) {
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
        
        if (\function_exists('putenv')) {
            @putenv('SHELL_VERBOSITY='.$shellVerbosity);
        }
        
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }
}