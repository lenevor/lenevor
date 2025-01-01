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

namespace Syscodes\Components\Console\Output;

use Syscodes\Components\Contracts\Console\Output\OutputFormatter;
use Syscodes\Components\Contracts\Console\Output\Output as OutputInterface;
use Syscodes\Components\Contracts\Console\Output\ConsoleOutput as ConsoleOutputInterface;
use Syscodes\Components\Contracts\Console\Output\OutputFormatter as OutputFormatterInterface;

/**
 * The ConsoleOutput is the default class for all CLI ouput using STDOUT and STDERR.
 */
class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    /**
     * Get the stderr for console output.
     * 
     * @var resource|object $stderr
     */
    protected $stderr;

    /**
     * Constructor. Create a new StreamOutput instance.
     * 
     * @param  int  $verbosity  The verbosity level
     * @param  bool|null  $decorated  Whether to decorated messages
     * @param  \Syscodes\Components\Contracts\Console\Output\OutputFormatter|null  $formatter  The output formatter instance
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, ?bool $decorated = null, ?OutputFormatter $formatter = null)
    {
        parent::__construct($this->OpenOutputStream(), $verbosity, $decorated, $formatter);

        if (null === $formatter) {
            $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated);

            return;
        }
        
        $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated, $this->getFormatter());

        if (null === $decorated) {
            $this->setDecorated($this->getDecorated() && $this->stderr->getDecorated());
        }
    }
    
    /**
     * Sets the decorated flag.
	 * 
	 * @param  bool  $decorated  Whether to decorated messages
	 * 
	 * @return void
     */
    public function setDecorated(bool $decorated): void
    {
        parent::setDecorated($decorated);
        
        $this->stderr->setDecorated($decorated);
    }
    
    /**
     * Sets a output formatter instance.
	 * 
	 * @param  \Syscodes\Components\Contracts\Console\Output\OutputFormatter  $formatter;
	 * 
	 * @return void
     */
    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        parent::setFormatter($formatter);
        
        $this->stderr->setFormatter($formatter);
    }
    
    /**
     * Sets the verbosity of the output.
	 * 
	 * @param  int  $level
	 * 
	 * @return void
     */
    public function setVerbosity(int $level): void
    {
        parent::setVerbosity($level);
        
        $this->stderr->setVerbosity($level);
    }
    
    /**
     * Gets the Output interface for errors.
     * 
     * @return \Syscodes\Components\Contracts\Console\Output\Output
     */
    public function getErrorOutput()
    {
        return $this->stderr;
    }
    
    /**
     * Sets the Output interface for errors.
     * 
     * @param  Syscodes\Components\Contracts\Console\Output\Output  $error
     * 
     * @return \Syscodes\Components\Contracts\Console\Output\Output
     */
    public function SetErrorOutput(OutputInterface $error): void
    {
        $this->stderr = $error;
    }
    
    /**
     * Gets the open output to given stream.
     * 
     * @return resource
     */
    protected function openOutputStream()
    {
        return @fopen('php://stdout', 'w') ?: @fopen('php://output', 'w');
    }
    
    /**
     * Gets the open output to a given error stream.
     * 
     * @return resource
     */
    protected function openErrorStream()
    {
        return @fopen('php://stderr', 'w');
    }
}