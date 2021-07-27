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

namespace Syscodes\Console\Output;

use Syscodes\Contracts\Console\Output as OutputInterface;
use Syscodes\Contracts\Console\ConsoleOutput as ConsoleOutputInterface;
use Syscodes\Contracts\Console\OutputFormatter as OutputFormatterInterface;

/**
 * @author Alexander Campo <jalexcam@gmail.com>
 */
class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    /**
     * Get the stderr for console output.
     * 
     * @var resource $stderr
     */
    protected $stderr;

    /**
     * Constructor. Create a new StreamOutput instance.
     * 
     * @param  bool|null  $decorated  Whether to decorated messages
     * @param  \Syscodes\Contracts\Console\OutputFormatter|null  $formatter  The output formatter instance
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException
     */
    public function __construct(bool $decorated = false, OutputFormatter $formatter = null)
    {
        parent::__construct($this->OpenOutputStream(), $decorated, $formatter);

        if (null === $formatter) {
            $this->stderr = new StreamOutput($this->openErrorStream(), $decorated);

            return;
        }
        
        $this->stderr = new StreamOutput($this->openErrorStream(), $decorated, $this->getFormatter());

        if (null === $decorated) {
            $this->setDecorated($this->stderr->getDecorated());
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
	 * @param  \Syscodes\Contracts\Console\OutputFormatter  $formatter;
	 * 
	 * @return void
	 */
	public function setFormatter(OutputFormatterInterface $formatter): void
    {
        parent::setFormatter($formatter);

        $this->stderr->setFormatter($formatter);
    }

    /**
     * Gets the Output interface for errors.
     * 
     * @return \Syscodes\Contracts\Console\Output
     */
    public function getErrorOutput(): OutputInterface
    {
        return $this->stderr;
    }

    /**
     * Sets the Output interface for errors.
     * 
     * @param  \Syscodes\Contracts\Console\Output  $error
     * 
     * @return \Syscodes\Contracts\Console\Output
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