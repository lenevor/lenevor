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

namespace Syscodes\Console;

use Exception;
use Syscodes\Console\Input\ArgvInput;
use Syscodes\Console\Output\ConsoleOutput;
use Syscodes\Contracts\Console\Input as InputInterface;
use Syscodes\Contracts\Console\Output as OutputInterface;

/**
 * This is the main entry point of a Console application.
 * 
 * This class is optimized for a standard CLI environment.
 * 
 * @author Alexander Campo <jalexcam@gmail.com> 
 */
abstract class Console
{
    /**
     * Gets the name of the aplication.
     * 
     * @var string $name
     */
    protected $name;

    /**
     * Gets the version of the application.
     * 
     * @var string $version
     */
    protected $version;

    /**
     * Constructor. Create new Console instance.
     * 
     * @param  string  $name  The console name
     * @param  string  $version  The console version
     * 
     * @return void
     */
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        $this->name    = $name;
        $this->version = $version;
    }

    /**
     * Gets the name of the application.
     * 
     * @return string 
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the application.
     * 
     * @param  string  $name  The application name
     * 
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Gets the version of the application.
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the name of the application.
     * 
     * @param  string  $version  The application version
     * 
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
	 * Runs the current command discovered on the CLI.
	 * 
	 * @param  \Syscodes\Contracts\Console\Input|null  $input  The input interface implemented
	 * @param  \Syscodes\Contracts\Console\Output|null  $output  The output interface implemented
	 *
	 * @return int
	 */
	public function run(InputInterface $input = null, OutputInterface $output = null)
	{
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        try {
            $this->doExecute($input, $output);
        } catch (Exception $e) {
            throw $e;
        }
	}

    /**
     * Executes the current application of console.
     * 
     * @param  \Syscodes\Contracts\Console\Input  $input  The input interface implemented
	 * @param  \Syscodes\Contracts\Console\Output  $output  The output interface implemented
     * 
     * @return int
     */
    public function doExecute(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(['--version', '-V'], true)) {
            $output->writeln($this->getLongVersion());

            return 0;
        }

        $output->write('<info>Hello world!</info>');
		$output->hr(1);
        $output->write('<yellow>Hello world!</yellow>');
    }

    /**
     * Returns the long version of the application.
     *
     * @return string
     */
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return sprintf('%s <cyan>%s</cyan>', $this->getName(), $this->getVersion());
            }

            return $this->getName();
        }

        return 'Lenevor CLI Console';
    }
}