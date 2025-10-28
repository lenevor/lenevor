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

namespace Syscodes\Components\Console\Concerns;

use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;

/**
 * For specify arguments and options on the command.
 */
trait HasParameters
{
    /**
     * Specify the arguments and options on the command.
     * 
     * @return void
     */
    protected function specifyParameters()
    {
        foreach ($this->getArguments() as $arguments) {
            if ($arguments instanceof InputArgument) {
                $this->getDefinition()->addArgument($arguments);
            } else {
                $this->addArgument(...$arguments);
            }
        }
        
        foreach ($this->getOptions() as $options) {
            if ($options instanceof InputOption) {
                $this->getDefinition()->addOption($options);
            } else {
                $this->addOption(...$options);
            }
        }
    }
    
    /**
     * Get the console command arguments.
     * 
     * @return array
     */
    protected function getArguments(): array
    {
        return [];
    }
    
    /**
     * Get the console command options.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }
}