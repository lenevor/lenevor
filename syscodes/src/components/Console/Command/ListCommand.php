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
 * This class displays the list of all available commands 
 * enabled for the application.
 */
class ListCommand extends Command
{
    /**
     * Gets input definition for command.
     * 
     * @return void
     */
    protected function define()
    {
        $this
            ->setName('list')
            ->setDefinition([
                new InputArgument('namespace', InputArgumentInterface::OPTIONAL, 'The namespace name'),
                new InputOption('raw', null, InputOptionInterface::VALUE_NONE, 'To output raw command list'),
                new InputOption('format', null, InputOptionInterface::VALUE_REQUIRED, 'The output format (txt, xml, json)', 'txt'),
            ])
            ->setDescription('List commands')
            ->setHelp(<<<'EOF'
            The <comment>%command-name%</> command lists all commands:
            
                <success> %command-fullname% </>
            
            You can also display the commands for a specific namespace:
                
                <success> %command-fullname% test </>
            
            You can also output the successrmation in other formats by using the <comment>--format</> option:
                
                <success> %command-fullname% --format=xml </>
                
            It's also possible to get raw list of commands (useful for embedding command runner):
                
                <success> %command-fullname% --raw </>
            EOF
            );
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
        $helper = new DescriptorHelper();
        $helper->describe($output, $this->getApplication(), [
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
        ]);

        return 0;
    }
}