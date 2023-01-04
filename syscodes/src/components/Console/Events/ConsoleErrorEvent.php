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
 * @copyright   Copyright (c) 2019 - 2023 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Console\Events;

use Throwable;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Contracts\Console\Input\Input as InputInterface;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * Allows to handle throwables running a command.
 */
class ConsoleErrorEvent extends ConsoleEvent
{
    /**
     * Gets the error of handler.
     * 
     * @var \Throwable $error
     */
    protected $error;

    /**
     * Gets the exit error as integer.
     * 
     * @var int $exitCode
     */
    protected $exitCode;

    /**
     * Constructor. The create a new ConsoleErrorEvent instance.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Input\input  $input
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
     * @param  \Throwable  $error
     * @param  \Syscodes\Components\Console\Command\Command  $command
     * 
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output, Throwable $error, Command $command)
    {
        parent::__construct($command, $input, $output);

        $this->setError($error);
    }

    /**
     * Gets the error of handler into throwables.
     * 
     * @return \Throwable
     */
    public function getError(): Throwable
    {
        return $this->error;
    }

    /**
     * Sets the error of handler into throwables.
     * 
     * @param  \Throwable  $error
     * 
     * @return void
     */
    public function setError(Throwable $error): void
    {
        $this->error = $error;
    }
}