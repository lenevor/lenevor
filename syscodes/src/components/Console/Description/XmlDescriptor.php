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

namespace Syscodes\Components\Console\Description;

use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Input\InputDefinition;

/**
 * Xml descriptor.
 */
class XmlDescriptor extends Descriptor
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Components\Contracts\Console\Output $output
     */
    protected $output;

    /**
     * Describes an InputArgument instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputArgument  $argument  The argument implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    protected function describeArgument(InputArgument $argument, array $options = [])
    {
        $this->writeText('hola mundo!!!');
    }

    /**
     * Describes an InputOption instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputOption  $option  The option implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    protected function describeOption(InputOption $option, array $options = [])
    {
        $this->writeText('hola mundo!!!');
    }

    /**
     * Describes an InputDefinition instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputDefinition  $definition  The definition implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    protected function describeDefinition(InputDefinition $definition, array $options = [])
    {
        $this->writeText('hola mundo!!!');
    }
    
    /**
     * Describes an Command instance.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command  The command implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    protected function describeCommand(Command $command, array $options = [])
    {
        $this->writeText(' Hola command!!! ');
    }

    /**
     * Describes an Application instance.
     * 
     * @param  \Syscodes\Components\Console\Application  $application  The application implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    protected function describeApplication(Application $application, array $options = [])
    {
        $this->writeText('<fg=bright-green;bg=green> Hola application!!! </>');
    } 

    /**
     * Writes a message to the output.
     * 
     * @param  string  $content  The message to output
     * 
     * @return string
     */
    private function writeText(string $content)
    {
        $this->write($content);
    }
}