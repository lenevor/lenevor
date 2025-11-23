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

namespace Syscodes\Components\Console;

use Syscodes\Components\Contracts\Console\NewLineInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Decorates output to add console style guide helpers.
 */
class OutputStyle extends SymfonyStyle implements NewLineInterface
{
    /**
     * The output instance.
     * 
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;
    
    /**
     * If the last output written wrote a new line.
     * 
     * @var bool
     */
    protected $newLineWritten = false;
    
    /**
     * The number of trailing new lines written by the last output.
     * 
     * @var int
     */
    protected $newLinesWritten = 1;

    /**
     * Constructor. Create a new OutputStyle class instance.
     * 
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * 
     * @return void
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        parent::__construct($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function write(string|iterable $messages, bool $newline = false, int $options = 0): void
    {
        $this->newLinesWritten = $this->trailingNewLineCount($messages) + (int) $newline;
        $this->newLineWritten = $this->newLinesWritten > 0;

        parent::write($messages, $newline, $options);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function writeln(string|iterable $messages, int $type = self::OUTPUT_NORMAL): void
    {
        if ($this->output->getVerbosity() >= $type) {
            $this->newLinesWritten = $this->trailingNewLineCount($messages) + 1;
            $this->newLineWritten = true;
        }

        parent::writeln($messages, $type);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function newLine(int $count = 1): void
    {
        $this->newLinesWritten += $count;
        $this->newLineWritten = $this->newLinesWritten > 0;

        parent::newLine($count);
    }

    /**
     * {@inheritdoc}
     */
    public function newLinesWritten(): int
    {
        if ($this->output instanceof static) {
            return $this->output->newLinesWritten();
        }

        return $this->newLinesWritten;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated use newLinesWritten
     */
    public function newLineWritten(): bool
    {
        if ($this->output instanceof static && $this->output->newLineWritten()) {
            return true;
        }

        return $this->newLineWritten;
    }

    /**
     * Count the number of trailing new lines in a string.
     *
     * @param  string|iterable  $messages
     * 
     * @return int
     */
    protected function trailingNewLineCount($messages)
    {
        if (is_iterable($messages)) {
            $string = '';

            foreach ($messages as $message) {
                $string .= $message.PHP_EOL;
            }
        } else {
            $string = $messages;
        }

        return strlen($string) - strlen(rtrim($string, PHP_EOL));
    }

    /**
     * Returns whether verbosity is quiet (-q).
     *
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }

    /**
     * Get the underlying Symfony output implementation.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}