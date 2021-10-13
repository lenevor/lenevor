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

namespace Syscodes\Components\Contracts\Console;

use Syscodes\Components\Contracts\Console\Output as OutputInterface;

/**
 * Descriptor interface.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
interface Descriptor
{
    /**
     * Describes the type of input, output and command for console.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Output  $output
     * @param  object  $object
     * @param  array  $options
     * 
     * @return void
     */
    public function describe(OutputInterface $output, object $object, array $options = []);
}