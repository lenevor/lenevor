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

namespace Syscodes\Components\Core\Console\Commands;

use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Input\InputOption as InputOptionInterface;

/**
 * This class displays the key generate for a given command.
 */
class KeyGenerateCommand extends Command
{
    /**
     * Gets input definition for command.
     * 
     * @return void
     */
    protected function define()
    {
        $this
            ->setName('key:generate')
            ->setDefinition([
                new InputOption('show', null, InputOptionInterface::VALUE_REQUIRED, 'Display the key instead of modifying files'),
            ])
            ->setDescription('Set the application key');
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

    }
}