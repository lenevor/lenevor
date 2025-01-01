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

namespace Syscodes\Components\Contracts\Console\Output;

use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;

/**
 * <ConsoleOutput> is the interface implemented by ConsoleOutput class.
 * This adds information about stderr and output stream.
 */
interface ConsoleOutput extends OutputInterface
{
    /**
     * Gets the Output interface for errors.
     * 
     * @return \Syscodes\Components\Contracts\Console\Output\Output
     */
    public function getErrorOutput();

    /**
     * Sets the Output interface for errors.
     * 
     * @param  Syscodes\Components\Contracts\Console\Output\Output  $error
     * 
     * @return \Syscodes\Components\Contracts\Console\Output\Output
     */
    public function SetErrorOutput(OutputInterface $error): void;
}