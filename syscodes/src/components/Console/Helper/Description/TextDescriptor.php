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

namespace Syscodes\Components\Console\Helper\Description;

use Syscodes\Components\Console\Application;
use Syscodes\Components\Console\Command\Command;
use Syscodes\Components\Console\Input\InputOption;
use Syscodes\Components\Console\Input\InputArgument;
use Syscodes\Components\Console\Input\InputDefinition;

/**
 * Text descriptor.
 */
class TextDescriptor extends Descriptor
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
        if (null !== $argument->getDefault() && ( ! is_array($argument->getDefault()) || count($argument->getDefault()))) {
            $default = sprintf(' [<note>default: %s</>] ', $argument->getDefault());
        } else {
            $default = '';
        }

        $totalWidth = strlen($argument->getName());
        $spacingWidth = $totalWidth - strlen($argument->getName());

        $this->writeText(sprintf('  <info>%s</>  %s%s%s',
            $argument->getName(),
            str_repeat(' ', $spacingWidth),
            preg_replace('/\s*[\r\n]\s*/', "\n".str_repeat(' ', $totalWidth + 4), $argument->getDescription()),
            $default
        ), $options);
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
        if ($option->isAcceptValue() && null !== $option->getDefault() && ( ! is_array($option->getDefault()) || count($option->getDefault()))) {
            $default = sprintf(' [<note>default: %s</>] ', $option->getDefault());
        } else {
            $default = '';
        }

        $value = '';

        if ($option->isAcceptValue()) {
            $value = '='.strtoupper($option->getName());

            if ($option->isValueOptional()) {
                $value = '['.$value.']';
            }
        }

        $synopsis = sprintf('%s%s',
            $option->getShortcut() ? sprintf('-%s, ', $option->getShortcut()) : '    ',
            sprintf($option->isNegatable() ? '--%1$s|--no-%1$s' : '--%1$s%2$s', $option->getName(), $value)
        );

        $spacingWidth = (20 - strlen($synopsis));

        $this->writeText(sprintf('  <fg=green>%s</>  %s%s%s%s',
            $synopsis,
            str_repeat(' ', $spacingWidth),
            preg_replace('/\s*[\r\n]\s*/', "\n".str_repeat(' ', 4), $option->getDescription()),
            $default,
            $option->isArray() ? '<info> (multiple values allowed)</>' : ''
        ), $options);
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
        if ($definition->getArguments()) {
            $this->writeText('<comment>Arguments:</>', $options);
            $this->writeText("\n");

            foreach ($definition->getArguments() as $argument) {
                $this->describeArgument($argument, $options);
                $this->writeText("\n");
            }
        }

        if ($definition->getArguments() && $definition->getOptions()) {
            $this->writeText("\n");
        }

        if ($definition->getOptions()) {
            $laterOptions = [];

            $this->writeText('<comment>Options:</>', $options);

            foreach ($definition->getOptions() as $option) {
                if (\strlen($option->getShortcut() ?? '') > 1) {
                    $laterOptions[] = $option;
                    continue;
                }
                $this->writeText("\n");
                $this->describeOption($option, $options);
            }

            foreach ($laterOptions as $option) {
                $this->writeText("\n");
                $this->describeOption($option, $options);
            }
        }
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
        $command->mergeApplicationDefinition(false);

        $this->writeText($command->getApplication()->getConsoleVersion());
        $this->writeText("\n\n");

        if ($description = $command->getDescription()) {
            $this->writeText('<comment>Description:</>', $options);
            $this->writeText("\n");
            $this->writeText('  '.$description);
            $this->writeText("\n\n");
        }
        
        $this->writeText('<comment>Usage:</>', $options);
        
        foreach (array_merge([$command->getSynopsis(true)], $command->getAliases(), $command->getUsages()) as $usage) {
            $this->writeText("\n");
            $this->writeText('  '.$usage, $options);
        }
        
        $this->writeText("\n\n");

        $definition = $command->getDefinition();
        
        if ($definition->getOptions() || $definition->getArguments()) {
            $this->describeDefinition($definition, $options);
            $this->writeText("\n");
        }

        $help = $command->getProccesedHelp();

        if ($help && $help !== $description) {
            $this->writeText("\n");
            $this->writeText('<comment>Help:</>', $options);
            $this->writeText("\n");
            $this->writeText('  '.str_replace("\n", "\n  ", $help), $options);
            $this->writeText("\n");
        }
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
        if ('' != $help = $application->getHelp()) {
            $this->writeText("$help\n\n", $options);
        }
        
        $this->writeText("<comment>Usage:</>\n", $options);
        $this->writeText("  command [options] [arguments]\n\n", $options);

        $this->describeDefinition(new InputDefinition($application->getDefinition()->getOptions()), $options);

        $this->writeText("\n");
    }

    /**
     * Writes a message to the output.
     * 
     * @param  string  $content  The message to output
     * @param  array  $options  The option of bitmask
     * 
     * @return string
     */
    private function writeText(string $content, array $options = [])
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            isset($options['raw_output']) ? ! $options['raw_output'] : true
        );
    }
}