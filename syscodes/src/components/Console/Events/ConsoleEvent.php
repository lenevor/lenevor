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
 
namespace Syscodes\Components\Console\Events;

use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Events\Dispatcher as Event;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * This class allows to inpect input and output of a command.
 */
class ConsoleEvent extends Event
{
    /**
     * Gets the command name.
     * 
     * @var \Syscodes\Components\Console\Command\Command $command
     */
    protected $command;

    /**
     * Gets the input of a command.
     * 
     * @var \Syscodes\Components\Contracts\Console\Input $input
     */
    private $input;

    /**
     * Gets the output of a command.
     * 
     * @var \Syscodes\Components\Contracts\Console\Output $output
     */
    private $output;

    /**
     * Constructor. The create new ConsoleEvent instance.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * @param  \Syscodes\Components\Contracts\Console\Input\Input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
     * 
     * @return void
     */
    public function __construct(?Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input   = $input;
        $this->output  = $output;
    }

    /**
     * Gets the command to executed.
     * 
     * @return \Syscodes\Components\Console\Command\Command
     */
    public function getCommand(): ?Command
    {
        return $this->command;
    }

    /**
     * Gets the command input instance.
     * 
     * @return \Syscodes\Components\Contracts\Console\Input\Input
     */
    public function getCommandInput()
    {
        return $this->input;
    }

    /**
     * Gets the command output instance.
     * 
     * @return \Syscodes\Components\Contracts\Console\Output\Output
     */
    public function getCommandOutput()
    {
        return $this->output;
    }
}