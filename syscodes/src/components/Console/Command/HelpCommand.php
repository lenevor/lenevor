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

use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Helper\DescriptorHelper;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Input\InputOption as InputOptionInterface;
use Syscodes\Components\Contracts\Console\Input\InputArgument as InputArgumentInterface;

/**
 * This class displays the help for a given command.
 */
class HelpCommand extends Command
{
    /**
     * The command implement.
     * 
     * @var \Syscodes\Components\Console\Command\Command $command
     */
    protected $command;

    /**
     * Gets input definition for command.
     * 
     * @return void
     */
    protected function define()
    {
        $this
            ->setName('help')
            ->setDefinition([
                new InputArgument('command_name', InputArgumentInterface::OPTIONAL, 'The command name', 'help'),
                new InputOption('format', null, InputOptionInterface::VALUE_REQUIRED, 'The output format (txt, xml, json)', 'txt'),
                new InputOption('raw', null, InputOptionInterface::VALUE_NONE, 'To output raw command help'),
            ])
            ->setDescription('Display help for a command')
            ->setHelp(<<<'EOF'
            The <comment>%command-name%</> command displays help for a given command:
                
                <success> %command-fullname% list </>
                
            You can also output the help in other formats by using the <comment>--format</> option:
                
                <success> %command-fullname% --format=xml list </>
                
            To display the list of available commands, please use the <comment>list</> command.
            EOF
            );
    }

    /**
     * Sets the command.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * 
     * @return void
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Executes the current command.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $input
     * 
     * @return int|mixed
     * 
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->findCommand($input->getArgument('command_name'));
        }
        
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
        ]);
        
        $this->command = null;

        return 0;
    }
}