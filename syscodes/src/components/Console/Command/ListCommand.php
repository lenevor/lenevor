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
use Syscodes\Console\Helper\DescriptorHelper;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Contracts\Console\InputOption as InputOptionInterface;

/**
 * This class displays the list of all available commands 
 * enabled for the application. 
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ListCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('list')
            ->setDefinition([
                new InputOption('raw', null, InputOptionInterface::VALUE_NONE, 'To output raw command list'),
                new InputOption('format', null, InputOptionInterface::VALUE_REQUIRED, 'The output format (txt, xml, json)', 'txt'),
            ])
            ->setDescription('List commands')
            ->setHelp('List command...');
    }

    /**
     * {@inheritdoc}
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