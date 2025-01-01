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

/**
 * Allows to do things before the command is executed.
 */
final class ConsoleCommandEvent extends ConsoleEvent
{
    /**
     * Indicates if the command should be run or skipped.
     * 
     * @var bool $commandShouldRunSkipped
     */
    private $commandShouldRunSkipped = true;
    
    /**
     * Disables the command.
     * 
     * @return bool
     */
    public function disableCommand(): bool
    {
        return $this->commandShouldRunSkipped = false;
    }
    
    /**
     * Enabled the command.
     * 
     * @return bool
     */
    public function enableCommand(): bool
    {
        return $this->commandShouldRunSkipped = true;
    }
    
    /**
     * Returns true if the command is runnable, false otherwise.
     * 
     * @return bool
     */
    public function commandShouldRunSkipped(): bool
    {
        return $this->commandShouldRunSkipped;
    }
}