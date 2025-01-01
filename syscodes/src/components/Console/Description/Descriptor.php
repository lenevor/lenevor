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

use InvalidArgumentException;
use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Input\InputDefinition;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Description\Descriptor as DescriptorInterface;

/**
 * This class allows all console description variables to be displayed.
 */
abstract class Descriptor implements DescriptorInterface
{
    /**
     * The ouput command for console.
     * 
     * @var \Syscodes\Components\Contracts\Console\Output\Output $output
     */
    protected $output;
    
    /**
     * Describes the type of input, output and command for console.
     * 
     * @param  \Syscodes\Components\Contracts\Console\Output\Output  $output
     * @param  object  $object
     * @param  array  $options
     * 
     * @return void
     */
    public function describe(OutputInterface $output, object $object, array $options = []): void
    {
        $this->output = $output;

        match (true) {
            $object instanceof InputArgument => $this->describeArgument($object, $options),
            $object instanceof InputOption => $this->describeOption($object, $options),
            $object instanceof InputDefinition => $this->describeDefinition($object, $options),
            $object instanceof Command => $this->describeCommand($object, $options),
            $object instanceof Application => $this->describeApplication($object, $options),
            default => throw new InvalidArgumentException(sprintf('Object of type "%s" is not describable.', get_debug_type($object))),
        };
    }

    /**
     * Writes a message to the output.
     * 
     * @param  string  $message  The message to output
     * @param  bool  $option  The option of bitmask
     * 
     * @return string
     */
    protected function write(string $message, bool $option = false)
    {
        $this->output->write($message, false, $option ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW);
    }

    /**
     * Describes an InputArgument instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputArgument  $argument  The argument implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    abstract protected function describeArgument(InputArgument $argument, array $options = []);

    /**
     * Describes an InputOption instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputOption  $option  The option implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    abstract protected function describeOption(InputOption $option, array $options = []);

    /**
     * Describes an InputDefinition instance.
     * 
     * @param  \Syscodes\Components\Console\Input\InputDefinition  $definition  The definition implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    abstract protected function describeDefinition(InputDefinition $definition, array $options = []);

    /**
     * Describes an Command instance.
     * 
     * @param  \Syscodes\Components\Console\Command\Command  $command  The command implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    abstract protected function describeCommand(Command $command, array $options = []);

    /**
     * Describes an Application instance.
     * 
     * @param  \Syscodes\Components\Console\Application  $application  The application implemented
     * @param  array  $options  The options of the console
     * 
     * @return void
     */
    abstract protected function describeApplication(Application $application, array $options = []);
}