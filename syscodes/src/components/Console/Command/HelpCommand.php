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

use Syscodes\Console\Input\InputOption;
use Syscodes\Console\Input\InputArgument;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Contracts\Console\InputOption as InputOptionInterface;
use Syscodes\Contracts\Console\InputArgument as InputArgumentInterface;

/**
 * This class displays the help for a given command.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class HelpCommand extends Command
{
    /**
     * The command implement.
     * 
     * @var \Syscodes\Console\Command\Command $command
     */
    protected $command;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('help')
            ->setDefinition([
                new InputArgument('command_name', InputArgumentInterface::OPTIONAL, 'The command name', 'help'),
            ])
            ->setDescription('Display help for a command')
            ->setHelp("Help command...");
    }

    /**
     * Sets the command.
     * 
     * @param  \Syscodes\Console\Command\Command  $command
     * 
     * @return void
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->findCommand($input->getArgument('command_name'));
        }
        
        $output->writeln($this->getApplication()->getConsoleVersion());
        $output->writeln("\nProbando ayuda....");
        
        $this->command = null;

        return 0;
    }
}