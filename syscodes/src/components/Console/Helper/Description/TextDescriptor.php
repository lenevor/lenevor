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

namespace Syscodes\Console\Helper\Description;

use Syscodes\Console\Application;
use Syscodes\Console\Command\Command;
use Syscodes\Console\Input\InputOption;
use Syscodes\Console\Input\InputArgument;
use Syscodes\Console\Input\InputDefinition;
use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Console\Helper\Description\ApplicationDescription;

/**
 * Text descriptor.
 * 
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class TextDescriptor extends Descriptor
{
    /**
     * The output interface implementation.
     * 
     * @var \Syscodes\Contracts\Console\Output $output
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function describeArgument(InputArgument $argument, array $options = [])
    {
        if (null !== $argument->getDefault() && ( ! \is_array($argument->getDefault()) || \count($argument->getDefault()))) {
            $default = sprintf(' [<info>default: %s</info>] ', $argument->getDefault());
        } else {
            $default = '';
        }

        $totalWidth = \strlen($argument->getName());
        $spacingWidth = $totalWidth - \strlen($argument->getName());

        $this->writeText(sprintf('  <green>%s</green>  %s%s%s',
            $argument->getName(),
            str_repeat(' ', $spacingWidth),
            preg_replace('/\s*[\r\n]\s*/', "\n".str_repeat(' ', $totalWidth + 4), $argument->getDescription()),
            $default
        ), $options);
    }

     /**
     * {@inheritdoc}
     */
    protected function describeOption(InputOption $option, array $options = [])
    {
        if ($option->isAcceptValue() && null !== $option->getDefault() && ( ! \is_array($option->getDefault()) || \count($option->getDefault()))) {
            $default = sprintf(' [<info>default: %s</info>] ', $option->getDefault());
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

        $spacingWidth = (20 - \strlen($synopsis));

        $this->writeText(sprintf('  <green>%s</green>  %s%s%s%s',
            $synopsis,
            str_repeat(' ', $spacingWidth),
            preg_replace('/\s*[\r\n]\s*/', "\n".str_repeat(' ', 4), $option->getDescription()),
            $default,
            $option->isArray() ? '<info> (multiple values allowed)</info>' : ''
        ), $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function describeDefinition(InputDefinition $definition, array $options = [])
    {
        if ($definition->getArguments()) {
            $this->writeText('<comment>Arguments:</comment>', $options);
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

            $this->writeText('<comment>Options:</comment>', $options);

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
     * {@inheritdoc}
     */
    protected function describeCommand(Command $command, array $options = [])
    {
        $command->mergeApplicationDefinition(false);

        $this->writeText($command->getApplication()->getConsoleVersion());
        $this->writeText("\n\n");

        if ($description = $command->getDescription()) {
            $this->writeText('<comment>Description:</comment>', $options);
            $this->writeText("\n");
            $this->writeText('  '.$description);
            $this->writeText("\n\n");
        }
        
        $this->writeText('<comment>Usage:</comment>', $options);
        
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
            $this->writeText('<comment>Help:</comment>', $options);
            $this->writeText("\n");
            $this->writeText('  '.str_replace("\n", "\n  ", $help), $options);
            $this->writeText("\n");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function describeApplication(Application $application, array $options = [])
    {
        if ('' != $help = $application->getHelp()) {
            $this->writeText("$help\n\n", $options);
        }
        
        $this->writeText("<comment>Usage:</comment>\n", $options);
        $this->writeText("  command [options] [arguments]\n\n", $options);

        $this->describeDefinition(new InputDefinition($application->getDefinition()->getOptions()), $options);

        $this->writeText("\n");
    }

    /**
     * {@inheritdoc}
     */
    private function writeText(string $content, array $options = [])
    {
        $this->write(
            isset($options['raw_text']) && $options['raw_text'] ? strip_tags($content) : $content,
            isset($options['raw_output']) ? !$options['raw_output'] : true
        );
    }
}